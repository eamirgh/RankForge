<?php

namespace Eamirgh\RankForge\Tests\Feature\Http;

use Eamirgh\RankForge\Facades\RankForge;
use Eamirgh\RankForge\Tests\TestCase;

class LlmsTxtRouteTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('rankforge.llms_txt.enabled', true);
    }

    public function test_it_serves_llms_txt_route(): void
    {
        RankForge::llmsTxt()
            ->title('Test App Docs')
            ->description('Testing LLMs route')
            ->addSection('Quickstart', 'Getting started', [
                ['title' => 'Start', 'url' => 'https://example.com/start'],
            ]);

        $response = $this->get('/llms.txt');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('# Test App Docs', $response->getContent());
        $this->assertStringContainsString('- [Start](https://example.com/start)', $response->getContent());
    }

    public function test_it_serves_llms_full_txt_route(): void
    {
        RankForge::llmsTxt()
            ->title('Test App Docs')
            ->addDocument('Doc 1', '<h1>Hello</h1><p>World</p>');

        $fullResponse = $this->get('/llms-full.txt');
        $fullResponse->assertStatus(200);
        $this->assertStringContainsString('text/plain', $fullResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('## Doc 1', $fullResponse->getContent());
        $this->assertStringContainsString('World', $fullResponse->getContent());
    }
}
