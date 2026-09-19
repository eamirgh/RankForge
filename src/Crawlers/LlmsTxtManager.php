<?php

namespace Eamirgh\RankForge\Crawlers;

use Eamirgh\RankForge\Crawlers\Transformers\ContentTransformer;

class LlmsTxtManager
{
    /** @var array<string, mixed> */
    protected array $config;

    protected ?string $title = null;

    protected ?string $description = null;

    /** @var array<string, array{description: string, links: array<array{title: string, url: string, description?: string|null}>}> */
    protected array $sections = [];

    /** @var array<array{title: string, content: string, url?: string|null}> */
    protected array $documents = [];

    protected bool $stripHtml = true;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->loadFromConfig();
    }

    protected function loadFromConfig(): void
    {
        $llmsConfig = (array) ($this->config['llms_txt'] ?? []);

        $this->title = $llmsConfig['title'] ?? $this->config['site_name'] ?? null;
        $this->description = $llmsConfig['description'] ?? ($this->config['description']['default'] ?? null);
        $this->stripHtml = (bool) ($llmsConfig['strip_html'] ?? true);

        $sections = (array) ($llmsConfig['sections'] ?? []);
        foreach ($sections as $sectionTitle => $sectionData) {
            $desc = is_array($sectionData) ? ($sectionData['description'] ?? '') : '';
            $links = is_array($sectionData) ? ($sectionData['links'] ?? []) : [];
            $this->addSection($sectionTitle, $desc, $links);
        }
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function stripHtml(bool $strip = true): static
    {
        $this->stripHtml = $strip;

        return $this;
    }

    /**
     * @param array<array{title: string, url: string, description?: string|null}> $links
     */
    public function addSection(string $title, string $description = '', array $links = []): static
    {
        $this->sections[$title] = [
            'description' => $description,
            'links' => $links,
        ];

        return $this;
    }

    public function addLink(string $sectionTitle, string $title, string $url, ?string $description = null): static
    {
        if (! isset($this->sections[$sectionTitle])) {
            $this->sections[$sectionTitle] = [
                'description' => '',
                'links' => [],
            ];
        }

        $link = ['title' => $title, 'url' => $url];
        if ($description !== null) {
            $link['description'] = $description;
        }

        $this->sections[$sectionTitle]['links'][] = $link;

        return $this;
    }

    public function addDocument(string $title, string $content, ?string $url = null): static
    {
        $this->documents[] = [
            'title' => $title,
            'content' => $content,
            'url' => $url,
        ];

        return $this;
    }

    public function addDirectory(string $path, ?string $prefixUrl = null, string $section = 'Documentation'): static
    {
        if (! is_dir($path)) {
            return $this;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                $ext = strtolower($fileInfo->getExtension());
                if (in_array($ext, ['md', 'markdown'], true)) {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        sort($files);

        $realBasePath = realpath($path) ?: $path;
        $realBasePath = rtrim(str_replace('\\', '/', $realBasePath), '/');

        foreach ($files as $filePath) {
            $content = (string) file_get_contents($filePath);
            $realFilePath = str_replace('\\', '/', realpath($filePath) ?: $filePath);

            if (str_starts_with($realFilePath, $realBasePath)) {
                $rel = ltrim(substr($realFilePath, strlen($realBasePath)), '/');
            } else {
                $rel = basename($filePath);
            }

            $url = $prefixUrl !== null
                ? rtrim($prefixUrl, '/').'/'.ltrim($rel, '/')
                : $rel;

            // Extract title: from `# Title` or first heading, or filename
            $title = null;
            if (preg_match('/^#+\s+(.+)$/m', $content, $headingMatch)) {
                $title = trim($headingMatch[1]);
            }
            if (empty($title)) {
                $title = pathinfo($filePath, PATHINFO_FILENAME);
            }

            // Extract description: from first paragraph or excerpt
            $body = preg_replace('/^---\s*[\r\n].*?[\r\n]---\s*[\r\n]/s', '', $content);
            $paragraphs = preg_split('/\n\s*\n/', (string) $body);
            $description = null;

            if (is_array($paragraphs)) {
                foreach ($paragraphs as $paragraph) {
                    $p = trim($paragraph);
                    if ($p === '' || str_starts_with($p, '#') || str_starts_with($p, '```')) {
                        continue;
                    }
                    $description = trim((string) preg_replace('/\s+/', ' ', $p));
                    break;
                }
            }

            $this->addLink($section, $title, $url, $description);
            $this->addDocument($title, $content, $url);
        }

        return $this;
    }

    public function render(): string
    {
        $lines = [];

        $title = $this->title ?? 'Site Documentation';
        $lines[] = "# {$title}";
        $lines[] = '';

        if (! empty($this->description)) {
            $lines[] = "> {$this->description}";
            $lines[] = '';
        }

        foreach ($this->sections as $sectionTitle => $sectionData) {
            $lines[] = "## {$sectionTitle}";
            $lines[] = '';

            if (! empty($sectionData['description'])) {
                $lines[] = $sectionData['description'];
                $lines[] = '';
            }

            foreach ($sectionData['links'] as $link) {
                $linkTitle = $link['title'] ?? ($link['name'] ?? 'Link');
                $linkUrl = $link['url'] ?? '#';
                $linkDesc = $link['description'] ?? ($link['desc'] ?? null);

                if (! empty($linkDesc)) {
                    $lines[] = "- [{$linkTitle}]({$linkUrl}): {$linkDesc}";
                } else {
                    $lines[] = "- [{$linkTitle}]({$linkUrl})";
                }
            }

            $lines[] = '';
        }

        return trim(implode("\n", $lines))."\n";
    }

    public function renderFull(): string
    {
        $lines = [];

        $title = $this->title ?? 'Site Documentation';
        $lines[] = "# {$title}";
        $lines[] = '';

        if (! empty($this->description)) {
            $lines[] = "> {$this->description}";
            $lines[] = '';
        }

        foreach ($this->documents as $doc) {
            $lines[] = "## {$doc['title']}";
            $lines[] = '';

            if (! empty($doc['url'])) {
                $lines[] = "Source: {$doc['url']}";
                $lines[] = '';
            }

            $content = $this->stripHtml
                ? ContentTransformer::toMarkdown($doc['content'])
                : $doc['content'];

            $lines[] = trim($content);
            $lines[] = '';
            $lines[] = '---';
            $lines[] = '';
        }

        return trim(implode("\n", $lines))."\n";
    }

    /**
     * @return array{llms.txt: string, llms-full.txt: string}
     */
    public function writeToDisk(?string $publicPath = null): array
    {
        $dir = $publicPath ?? (function_exists('public_path') ? public_path() : 'public');
        $dir = rtrim($dir, '/');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $llmsPath = "{$dir}/llms.txt";
        $fullPath = "{$dir}/llms-full.txt";

        file_put_contents($llmsPath, $this->render());
        file_put_contents($fullPath, $this->renderFull());

        return [
            'llms.txt' => $llmsPath,
            'llms-full.txt' => $fullPath,
        ];
    }
}
