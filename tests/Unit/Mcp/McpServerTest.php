<?php

namespace Eamirgh\RankForge\Tests\Unit\Mcp;

use Eamirgh\RankForge\Mcp\McpServer;
use Eamirgh\RankForge\Tests\TestCase;

class McpServerTest extends TestCase
{
    protected McpServer $server;

    protected function setUp(): void
    {
        parent::setUp();
        $this->server = new McpServer();
    }

    public function test_it_handles_initialize_request(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [],
                'clientInfo' => ['name' => 'test-client', 'version' => '1.0.0'],
            ],
        ]);

        $this->assertEquals('2.0', $response['jsonrpc']);
        $this->assertEquals(1, $response['id']);
        $this->assertArrayHasKey('result', $response);
        $this->assertEquals('rankforge-mcp', $response['result']['serverInfo']['name']);
        $this->assertArrayHasKey('tools', $response['result']['capabilities']);
        $this->assertArrayHasKey('resources', $response['result']['capabilities']);
    }

    public function test_it_handles_ping_request(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'ping',
        ]);

        $this->assertEquals('2.0', $response['jsonrpc']);
        $this->assertEquals(2, $response['id']);
        $this->assertEquals([], $response['result']);
    }

    public function test_it_lists_available_tools(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/list',
        ]);

        $this->assertArrayHasKey('result', $response);
        $tools = $response['result']['tools'];
        $toolNames = array_column($tools, 'name');

        $this->assertContains('check_seo_health', $toolNames);
        $this->assertContains('get_llms_txt', $toolNames);
        $this->assertContains('get_robots_txt', $toolNames);
        $this->assertContains('get_sitemap', $toolNames);
        $this->assertContains('inspect_html_meta', $toolNames);
        $this->assertContains('submit_indexnow', $toolNames);
        $this->assertContains('get_skill_md', $toolNames);
    }

    public function test_it_executes_check_seo_health_tool(): void
    {
        config()->set('rankforge.site_name', 'Test App');

        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'check_seo_health',
                'arguments' => [],
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $content = $response['result']['content'][0]['text'];
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('checks', $data);
    }

    public function test_it_executes_get_llms_txt_tool(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_llms_txt',
                'arguments' => ['full' => false],
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $content = $response['result']['content'][0]['text'];
        $this->assertStringContainsString('#', $content);
    }

    public function test_it_executes_get_robots_txt_tool(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_robots_txt',
                'arguments' => [],
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $content = $response['result']['content'][0]['text'];
        $this->assertStringContainsString('User-agent:', $content);
    }

    public function test_it_executes_inspect_html_meta_tool(): void
    {
        $html = '<!DOCTYPE html><html><head>'
            . '<title>Inspect Title</title>'
            . '<meta name="description" content="Inspect Desc">'
            . '<meta name="robots" content="noindex, follow">'
            . '<link rel="canonical" href="https://example.com/canonical">'
            . '<meta property="og:title" content="OG Title">'
            . '<meta name="twitter:card" content="summary_large_image">'
            . '</head><body></body></html>';

        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => [
                'name' => 'inspect_html_meta',
                'arguments' => ['html' => $html],
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $content = $response['result']['content'][0]['text'];
        $data = json_decode($content, true);

        $this->assertEquals('Inspect Title', $data['title']);
        $this->assertEquals('Inspect Desc', $data['description']);
        $this->assertEquals('noindex, follow', $data['robots']);
        $this->assertEquals('https://example.com/canonical', $data['canonical']);
        $this->assertEquals('OG Title', $data['open_graph']['og:title']);
        $this->assertEquals('summary_large_image', $data['twitter']['twitter:card']);
    }

    public function test_it_executes_get_skill_md_tool(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 77,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_skill_md',
                'arguments' => [],
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $content = $response['result']['content'][0]['text'];
        $this->assertStringContainsString('Skill: rankforge', $content);
    }

    public function test_it_lists_available_resources(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 8,
            'method' => 'resources/list',
        ]);

        $this->assertArrayHasKey('result', $response);
        $resources = $response['result']['resources'];
        $uris = array_column($resources, 'uri');

        $this->assertContains('rankforge://llms.txt', $uris);
        $this->assertContains('rankforge://llms-full.txt', $uris);
        $this->assertContains('rankforge://robots.txt', $uris);
        $this->assertContains('rankforge://sitemap.xml', $uris);
        $this->assertContains('rankforge://skill.md', $uris);
    }

    public function test_it_reads_resource(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 9,
            'method' => 'resources/read',
            'params' => [
                'uri' => 'rankforge://robots.txt',
            ],
        ]);

        $this->assertArrayHasKey('result', $response);
        $contents = $response['result']['contents'][0];

        $this->assertEquals('rankforge://robots.txt', $contents['uri']);
        $this->assertEquals('text/plain', $contents['mimeType']);
        $this->assertStringContainsString('User-agent:', $contents['text']);
    }

    public function test_it_returns_error_for_unknown_tool(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 10,
            'method' => 'tools/call',
            'params' => [
                'name' => 'non_existent_tool',
            ],
        ]);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(-32601, $response['error']['code']);
    }

    public function test_it_returns_error_for_unknown_resource(): void
    {
        $response = $this->server->handleRequest([
            'jsonrpc' => '2.0',
            'id' => 11,
            'method' => 'resources/read',
            'params' => [
                'uri' => 'rankforge://unknown',
            ],
        ]);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(-32602, $response['error']['code']);
    }
}
