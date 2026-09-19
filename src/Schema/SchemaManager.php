<?php

namespace RankForge\Schema;

use RankForge\RankForgeManager;
use RankForge\Schema\Types\AbstractType;

class SchemaManager
{
    /** @var array<AbstractType|Graph|array<string, mixed>> */
    protected array $schemas = [];

    protected ?Graph $defaultGraph = null;

    public function __construct(
        protected RankForgeManager $manager,
    ) {}

    /**
     * Add a schema or graph.
     */
    public function add(AbstractType|Graph $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /**
     * Add raw JSON-LD data.
     *
     * @param  array<string, mixed>  $data
     */
    public function addRaw(array $data): static
    {
        $this->schemas[] = $data;

        return $this;
    }

    /**
     * Get or set the shared Graph instance.
     */
    public function graph(?Graph $graph = null): Graph
    {
        if ($graph !== null) {
            $this->defaultGraph = $graph;
            if (! in_array($graph, $this->schemas, true)) {
                $this->schemas[] = $graph;
            }

            return $graph;
        }

        if ($this->defaultGraph === null) {
            $this->defaultGraph = new Graph();
            $this->schemas[] = $this->defaultGraph;
        }

        return $this->defaultGraph;
    }

    /**
     * @return array<AbstractType|Graph|array<string, mixed>>
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function hasSchemas(): bool
    {
        return $this->schemas !== [];
    }

    /**
     * Convert all schemas into an array representation.
     *
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return array_map(function (mixed $schema) {
            if ($schema instanceof AbstractType || $schema instanceof Graph) {
                return $schema->toArray();
            }

            return (array) $schema;
        }, $this->schemas);
    }

    /**
     * Render all registered JSON-LD schemas as <script> tags.
     */
    public function render(): string
    {
        if (! $this->manager->isJsonLdEnabled() || $this->schemas === []) {
            return '';
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if ($this->manager->isJsonLdPrettyPrint()) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $tags = [];

        foreach ($this->schemas as $schema) {
            $data = match (true) {
                $schema instanceof AbstractType, $schema instanceof Graph => $schema->toArray(),
                is_array($schema) => $schema,
                default => null,
            };

            if ($data === null) {
                continue;
            }

            $json = json_encode($data, $flags);

            if ($json === false) {
                continue;
            }

            // Prevent closing script tag injection
            $json = str_replace('</script', '<\/script', $json);

            $tags[] = '<script type="application/ld+json">'.$json.'</script>';
        }

        return implode(PHP_EOL, $tags);
    }
}
