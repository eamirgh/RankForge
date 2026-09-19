<?php

namespace Eamirgh\RankForge\Schema\Types;

class BreadcrumbList extends AbstractType
{
    /** @var array<int, array<string, mixed>> */
    protected array $itemList = [];

    public function __construct()
    {
        parent::__construct('BreadcrumbList');
    }

    public function add(string $name, string $url, ?int $position = null): static
    {
        $position ??= count($this->itemList) + 1;

        $this->itemList[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
            'item' => $url,
        ];

        $this->setProperty('itemListElement', $this->itemList);

        return $this;
    }

    public function fromUrl(?string $url = null): static
    {
        $url ??= (function_exists('request') ? request()->url() : '/');

        $parsed = parse_url($url);
        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'] ?? '';
        $baseUrl = $host !== '' ? "{$scheme}://{$host}" : '';

        if ($baseUrl !== '') {
            $this->add('Home', $baseUrl, 1);
        }

        $path = trim($parsed['path'] ?? '', '/');

        if ($path === '') {
            return $this;
        }

        $segments = explode('/', $path);
        $currentPath = $baseUrl;

        foreach ($segments as $index => $segment) {
            $currentPath .= '/'.$segment;
            $name = ucwords(str_replace(['-', '_'], ' ', urldecode($segment)));
            $position = $baseUrl !== '' ? $index + 2 : $index + 1;
            $this->add($name, $currentPath, $position);
        }

        return $this;
    }

    /**
     * @param  array<int, array{name: string, url: string, position?: int}>|array<string, string>  $items
     */
    public function items(array $items): static
    {
        foreach ($items as $key => $item) {
            if (is_array($item) && isset($item['name'], $item['url'])) {
                $this->add($item['name'], $item['url'], $item['position'] ?? null);
            } elseif (is_string($key) && is_string($item)) {
                $this->add($key, $item);
            }
        }

        return $this;
    }
}
