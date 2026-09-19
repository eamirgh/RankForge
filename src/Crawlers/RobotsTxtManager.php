<?php

namespace RankForge\Crawlers;

class RobotsTxtManager
{
    /** @var array<string, mixed> */
    protected array $config;

    /** @var array<string, array{allow: array<string>, disallow: array<string>}> */
    protected array $userAgents = [];

    /** @var array<string> */
    protected array $sitemaps = [];

    protected int|float|null $crawlDelay = null;

    protected ?string $host = null;

    protected ?bool $forceProduction = null;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->loadFromConfig();
    }

    protected function loadFromConfig(): void
    {
        $prodConfig = (array) ($this->config['robots_txt']['production'] ?? []);

        // Load user agents
        $agents = (array) ($prodConfig['user_agents'] ?? []);
        foreach ($agents as $agent => $rules) {
            $this->userAgents[$agent] = [
                'allow' => (array) ($rules['allow'] ?? []),
                'disallow' => (array) ($rules['disallow'] ?? []),
            ];
        }

        // Load AI crawlers
        $aiCrawlers = (array) ($prodConfig['ai_crawlers'] ?? []);
        foreach ($aiCrawlers as $agent => $rules) {
            $this->userAgents[$agent] = [
                'allow' => (array) ($rules['allow'] ?? []),
                'disallow' => (array) ($rules['disallow'] ?? []),
            ];
        }

        // Sitemaps
        $this->sitemaps = (array) ($this->config['robots_txt']['sitemaps'] ?? []);

        // Crawl delay & Host
        $this->crawlDelay = $this->config['robots_txt']['crawl_delay'] ?? null;
        $this->host = $this->config['robots_txt']['host'] ?? null;
    }

    public function userAgent(string $agent, array $rules): static
    {
        $this->userAgents[$agent] = [
            'allow' => (array) ($rules['allow'] ?? []),
            'disallow' => (array) ($rules['disallow'] ?? []),
        ];

        return $this;
    }

    public function allow(string|array $paths, string $userAgent = '*'): static
    {
        $paths = (array) $paths;

        if (! isset($this->userAgents[$userAgent])) {
            $this->userAgents[$userAgent] = ['allow' => [], 'disallow' => []];
        }

        $this->userAgents[$userAgent]['allow'] = array_values(array_unique(
            array_merge($this->userAgents[$userAgent]['allow'], $paths)
        ));

        return $this;
    }

    public function disallow(string|array $paths, string $userAgent = '*'): static
    {
        $paths = (array) $paths;

        if (! isset($this->userAgents[$userAgent])) {
            $this->userAgents[$userAgent] = ['allow' => [], 'disallow' => []];
        }

        $this->userAgents[$userAgent]['disallow'] = array_values(array_unique(
            array_merge($this->userAgents[$userAgent]['disallow'], $paths)
        ));

        return $this;
    }

    public function sitemap(string|array $sitemaps): static
    {
        $sitemaps = (array) $sitemaps;
        $this->sitemaps = array_values(array_unique(array_merge($this->sitemaps, $sitemaps)));

        return $this;
    }

    public function crawlDelay(int|float|null $delay): static
    {
        $this->crawlDelay = $delay;

        return $this;
    }

    public function host(?string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function forceProduction(bool $force = true): static
    {
        $this->forceProduction = $force;

        return $this;
    }

    public function isProduction(): bool
    {
        if ($this->forceProduction !== null) {
            return $this->forceProduction;
        }

        $noindexEnvs = (array) ($this->config['robots']['noindex_environments'] ?? ['local', 'testing', 'staging']);

        if (function_exists('app')) {
            $currentEnv = app()->environment();

            return ! in_array($currentEnv, $noindexEnvs, true);
        }

        return true;
    }

    public function render(): string
    {
        if (! $this->isProduction()) {
            return "User-agent: *\nDisallow: /\n";
        }

        $lines = [];

        foreach ($this->userAgents as $agent => $rules) {
            $lines[] = "User-agent: {$agent}";

            foreach ($rules['allow'] as $path) {
                $lines[] = "Allow: {$path}";
            }

            foreach ($rules['disallow'] as $path) {
                $lines[] = "Disallow: {$path}";
            }

            $lines[] = '';
        }

        if ($this->crawlDelay !== null) {
            $lines[] = "Crawl-delay: {$this->crawlDelay}";
        }

        if ($this->host !== null) {
            $lines[] = "Host: {$this->host}";
        }

        $sitemaps = $this->sitemaps;

        if ($sitemaps === [] && ($this->config['sitemap']['enabled'] ?? true)) {
            $defaultSitemap = function_exists('url') ? url('sitemap.xml') : 'http://localhost/sitemap.xml';
            $sitemaps = [$defaultSitemap];
        }

        foreach ($sitemaps as $sitemap) {
            $lines[] = "Sitemap: {$sitemap}";
        }

        $output = trim(implode("\n", $lines));

        return $output !== '' ? $output."\n" : '';
    }

    public function writeToDisk(?string $path = null): bool
    {
        $targetPath = $path;

        if ($targetPath === null) {
            $targetPath = function_exists('public_path') ? public_path('robots.txt') : 'public/robots.txt';
        }

        $directory = dirname($targetPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($targetPath, $this->render()) !== false;
    }
}
