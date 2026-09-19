<?php

namespace RankForge\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RankForge\Crawlers\LlmsTxtManager;

class LlmsTxtController extends Controller
{
    public function __construct(
        protected LlmsTxtManager $llmsTxtManager
    ) {}

    public function index(): Response
    {
        $content = $this->llmsTxtManager->render();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function full(): Response
    {
        $content = $this->llmsTxtManager->renderFull();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
