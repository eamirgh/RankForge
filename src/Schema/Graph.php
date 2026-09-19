<?php

namespace Eamirgh\RankForge\Schema;

use JsonSerializable;
use Eamirgh\RankForge\Schema\Types\AbstractType;

class Graph implements JsonSerializable
{
    /** @var AbstractType[] */
    protected array $schemas = [];

    public static function make(): static
    {
        return new static();
    }

    public function add(AbstractType $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /**
     * @return AbstractType[]
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function isEmpty(): bool
    {
        return $this->schemas === [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_map(function (AbstractType $schema) {
                $arr = $schema->toArray();
                unset($arr['@context']);

                return $arr;
            }, $this->schemas)),
        ];
    }

    public function toJson(?int $flags = null): string
    {
        $flags ??= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        return json_encode($this->toArray(), $flags) ?: '{}';
    }

    public function toScript(?int $flags = null): string
    {
        return '<script type="application/ld+json">'.$this->toJson($flags).'</script>';
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
