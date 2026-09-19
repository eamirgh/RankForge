<?php

namespace Eamirgh\RankForge\Mcp;

use Eamirgh\RankForge\Facades\RankForge;
use Throwable;

class McpServer
{
    /**
     * Handle an incoming JSON-RPC 2.0 request and return a response array.
     *
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    public function handleRequest(array $request): array
    {
        $id = $request['id'] ?? null;
        $method = $request['method'] ?? '';
        $params = $request['params'] ?? [];

        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize($params),
                'notifications/initialized' => [],
                'ping' => [],
                'tools/list' => $this->handleToolsList(),
                'tools/call' => $this->handleToolsCall($params),
                'resources/list' => $this->handleResourcesList(),
                'resources/read' => $this->handleResourcesRead($params),
                default => throw new McpException("Method not found: {$method}", -32601),
            };

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ];
        } catch (McpException $e) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ];
        } catch (Throwable $e) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error: '.$e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Start the stdio JSON-RPC server loop.
     */
    public function listen(): void
    {
        while (! feof(STDIN)) {
            $line = fgets(STDIN);

            if ($line === false || trim($line) === '') {
                continue;
            }

            $request = json_decode($line, true);

            if (! is_array($request)) {
                $errorResponse = [
                    'jsonrpc' => '2.0',
                    'id' => null,
                    'error' => [
                        'code' => -32700,
                        'message' => 'Parse error',
                    ],
                ];
                fwrite(STDOUT, json_encode($errorResponse)."\n");
                fflush(STDOUT);
                continue;
            }

            $response = $this->handleRequest($request);

            // Do not send response for JSON-RPC notifications (requests without id)
            if (array_key_exists('id', $request) && $request['id'] !== null) {
                fwrite(STDOUT, json_encode($response)."\n");
                fflush(STDOUT);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function handleInitialize(array $params): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => [
                    'listChanged' => false,
                ],
                'resources' => [
                    'subscribe' => false,
                    'listChanged' => false,
                ],
            ],
            'serverInfo' => [
                'name' => 'rankforge-mcp',
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function handleToolsList(): array
    {
        return [
            'tools' => [
                [
                    'name' => 'check_seo_health',
                    'description' => 'Perform an automated SEO health audit on the application configuration, robots.txt, sitemaps, and JSON-LD.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name' => 'get_llms_txt',
                    'description' => 'Generate and retrieve semantic Markdown documentation for AI crawlers (/llms.txt or /llms-full.txt).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'full' => [
                                'type' => 'boolean',
                                'description' => 'Whether to return the full unpaginated documentation bundle (llms-full.txt).',
                                'default' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'get_robots_txt',
                    'description' => 'Generate and retrieve the active robots.txt directives for web and AI crawlers.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name' => 'get_sitemap',
                    'description' => 'Retrieve the XML sitemap index or a specific chunked sitemap section.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'section' => [
                                'type' => 'string',
                                'description' => 'The sitemap section name (e.g. posts, products). Omit to get the root sitemap index.',
                            ],
                            'page' => [
                                'type' => 'integer',
                                'description' => 'Page number for chunked sitemaps (default: 1).',
                                'default' => 1,
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'inspect_html_meta',
                    'description' => 'Parse and inspect SEO metadata (title, description, canonical, robots, OG, Twitter) from raw HTML content.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'html' => [
                                'type' => 'string',
                                'description' => 'The raw HTML string to inspect.',
                            ],
                        ],
                        'required' => ['html'],
                    ],
                ],
                [
                    'name' => 'get_skill_md',
                    'description' => 'Retrieve the RankForge Agent Skill (SKILL.md) guidelines and code patterns.',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => (object) [],
                    ],
                ],
                [
                    'name' => 'submit_indexnow',
                    'description' => 'Submit URL(s) to the IndexNow protocol for instant search engine indexing (Bing, Yandex).',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'urls' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'List of absolute URLs to submit to IndexNow.',
                            ],
                        ],
                        'required' => ['urls'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function handleToolsCall(array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        $output = match ($name) {
            'check_seo_health' => $this->toolCheckSeoHealth(),
            'get_llms_txt' => $this->toolGetLlmsTxt($arguments),
            'get_robots_txt' => $this->toolGetRobotsTxt(),
            'get_sitemap' => $this->toolGetSitemap($arguments),
            'inspect_html_meta' => $this->toolInspectHtmlMeta($arguments),
            'submit_indexnow' => $this->toolSubmitIndexNow($arguments),
            'get_skill_md' => $this->toolGetSkillMd(),
            default => throw new McpException("Unknown tool: {$name}", -32601),
        };

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => is_string($output) ? $output : json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function toolCheckSeoHealth(): array
    {
        $siteName = config('rankforge.site_name');
        $titleDefault = config('rankforge.title.default');
        $descDefault = config('rankforge.description.default');
        $robotsEnabled = config('rankforge.robots_txt.enabled', true);
        $sitemapEnabled = config('rankforge.sitemap.enabled', true);
        $indexNowKey = config('rankforge.index_now.key');

        $checks = [
            'site_name' => [
                'status' => (! empty($siteName) && $siteName !== 'Laravel') ? 'OK' : 'WARN',
                'value' => $siteName,
            ],
            'title_default' => [
                'status' => ! empty($titleDefault) ? 'OK' : 'FAIL',
                'value' => $titleDefault,
            ],
            'description_default' => [
                'status' => ! empty($descDefault) ? 'OK' : 'FAIL',
                'value' => $descDefault,
            ],
            'robots_txt' => [
                'status' => $robotsEnabled ? 'OK' : 'WARN',
                'enabled' => $robotsEnabled,
            ],
            'sitemap' => [
                'status' => $sitemapEnabled ? 'OK' : 'WARN',
                'enabled' => $sitemapEnabled,
            ],
            'index_now' => [
                'status' => ! empty($indexNowKey) ? 'OK' : 'WARN',
                'configured' => ! empty($indexNowKey),
            ],
        ];

        $hasFail = in_array('FAIL', array_column($checks, 'status'), true);

        return [
            'status' => $hasFail ? 'FAIL' : 'PASS',
            'checks' => $checks,
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     */
    protected function toolGetLlmsTxt(array $args): string
    {
        $full = (bool) ($args['full'] ?? false);

        return $full
            ? RankForge::llmsTxt()->renderFull()
            : RankForge::llmsTxt()->render();
    }

    protected function toolGetRobotsTxt(): string
    {
        return RankForge::robotsTxt()->render();
    }

    /**
     * @param  array<string, mixed>  $args
     */
    protected function toolGetSitemap(array $args): string
    {
        $section = $args['section'] ?? null;
        $page = (int) ($args['page'] ?? 1);

        if ($section !== null && $section !== '') {
            return RankForge::sitemap()->renderSection($section, $page);
        }

        return RankForge::sitemap()->renderIndex();
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    protected function toolInspectHtmlMeta(array $args): array
    {
        $html = (string) ($args['html'] ?? '');

        $result = [
            'title' => null,
            'description' => null,
            'robots' => null,
            'canonical' => null,
            'open_graph' => [],
            'twitter' => [],
        ];

        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $result['title'] = trim($matches[1]);
        }

        if (preg_match('/<meta[^>]+name=[\'"]description[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches)) {
            $result['description'] = trim($matches[1]);
        }

        if (preg_match('/<meta[^>]+name=[\'"]robots[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches)) {
            $result['robots'] = trim($matches[1]);
        }

        if (preg_match('/<link[^>]+rel=[\'"]canonical[\'"][^>]+href=[\'"](.*?)[\'"]/is', $html, $matches)) {
            $result['canonical'] = trim($matches[1]);
        }

        if (preg_match_all('/<meta[^>]+property=[\'"](og:[^\'"]+)[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result['open_graph'][$match[1]] = trim($match[2]);
            }
        }

        if (preg_match_all('/<meta[^>]+name=[\'"](twitter:[^\'"]+)[\'"][^>]+content=[\'"](.*?)[\'"]/is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result['twitter'][$match[1]] = trim($match[2]);
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    protected function toolGetSkillMd(): string
    {
        $path = __DIR__.'/../../SKILL.md';
        return file_exists($path) ? (string) file_get_contents($path) : '';
    }

    protected function toolSubmitIndexNow(array $args): array
    {
        $urls = $args['urls'] ?? [];

        if (is_string($urls)) {
            $urls = [$urls];
        }

        $success = RankForge::indexNow()->submit($urls);

        return [
            'success' => $success,
            'submitted_urls' => $urls,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function handleResourcesList(): array
    {
        return [
            'resources' => [
                [
                    'uri' => 'rankforge://llms.txt',
                    'name' => 'llms.txt',
                    'description' => 'Concise semantic Markdown summary for LLMs and AI crawlers.',
                    'mimeType' => 'text/markdown',
                ],
                [
                    'uri' => 'rankforge://llms-full.txt',
                    'name' => 'llms-full.txt',
                    'description' => 'Comprehensive unpaginated Markdown documentation bundle for LLMs.',
                    'mimeType' => 'text/markdown',
                ],
                [
                    'uri' => 'rankforge://robots.txt',
                    'name' => 'robots.txt',
                    'description' => 'The active robots.txt directives including traditional and AI crawler rules.',
                    'mimeType' => 'text/plain',
                ],
                [
                    'uri' => 'rankforge://skill.md',
                    'name' => 'skill.md',
                    'description' => 'The RankForge agent skill instructions and guidelines.',
                    'mimeType' => 'text/markdown',
                ],
                [
                    'uri' => 'rankforge://sitemap.xml',
                    'name' => 'sitemap.xml',
                    'description' => 'The root XML sitemap index.',
                    'mimeType' => 'application/xml',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function handleResourcesRead(array $params): array
    {
        $uri = $params['uri'] ?? '';

        return match ($uri) {
            'rankforge://llms.txt' => [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'text/markdown',
                        'text' => RankForge::llmsTxt()->render(),
                    ],
                ],
            ],
            'rankforge://llms-full.txt' => [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'text/markdown',
                        'text' => RankForge::llmsTxt()->renderFull(),
                    ],
                ],
            ],
            'rankforge://robots.txt' => [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'text/plain',
                        'text' => RankForge::robotsTxt()->render(),
                    ],
                ],
            ],
            'rankforge://skill.md' => [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'text/markdown',
                        'text' => $this->toolGetSkillMd(),
                    ],
                ],
            ],
            'rankforge://sitemap.xml' => [
                'contents' => [
                    [
                        'uri' => $uri,
                        'mimeType' => 'application/xml',
                        'text' => RankForge::sitemap()->renderIndex(),
                    ],
                ],
            ],
            default => throw new McpException("Resource not found: {$uri}", -32602),
        };
    }
}
