<?php

namespace RankForge\Schema\Types;

use DateTimeInterface;
use JsonSerializable;
use RankForge\Schema\Graph;

abstract class AbstractType implements JsonSerializable
{
    protected string $type;

    /** @var array<string, mixed> */
    protected array $properties = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public static function make(): static
    {
        return new static();
    }

    /**
     * Set a property on the schema.
     */
    public function set(string $key, mixed $value): static
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Set a property on the schema (alias for set).
     */
    public function setProperty(string $key, mixed $value): static
    {
        return $this->set($key, $value);
    }

    /**
     * Get a property value.
     */
    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $this->properties[$key] ?? $default;
    }

    /**
     * Check if a property exists.
     */
    public function hasProperty(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }

    /**
     * Get all properties.
     *
     * @return array<string, mixed>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get the schema.org @type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Convert to a structured array including @context and @type.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
        ];

        foreach ($this->properties as $key => $value) {
            $resolved = $this->resolveValue($value);

            if ($resolved !== null) {
                $data[$key] = $resolved;
            }
        }

        return $data;
    }

    /**
     * Resolve any value (nested schemas, DateTime, arrays, etc.).
     */
    protected function resolveValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            $inner = $value->toArray();
            unset($inner['@context']);

            return $inner;
        }

        if ($value instanceof Graph) {
            $inner = $value->toArray();
            unset($inner['@context']);

            return $inner;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return $this->resolveArray($value);
        }

        return $value;
    }

    /**
     * Recursively resolve arrays that may contain AbstractType instances or DateTimes.
     *
     * @param  array<mixed>  $array
     * @return array<mixed>
     */
    protected function resolveArray(array $array): array
    {
        $result = [];

        foreach ($array as $key => $item) {
            $resolved = $this->resolveValue($item);

            if ($resolved !== null) {
                $result[$key] = $resolved;
            }
        }

        return $result;
    }

    /**
     * Convert schema to JSON string.
     */
    public function toJson(?int $flags = null): string
    {
        $flags ??= JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        return json_encode($this->toArray(), $flags) ?: '{}';
    }

    /**
     * Render as <script type="application/ld+json"> tag.
     */
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

    /**
     * Magic call for fluent setters: $schema->foo('bar').
     *
     * @param  array<mixed>  $arguments
     */
    public function __call(string $name, array $arguments): static
    {
        $this->set($name, $arguments[0] ?? true);

        return $this;
    }
}
