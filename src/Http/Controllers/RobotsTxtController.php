<?php

namespace RankForge\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RankForge\Crawlers\RobotsTxtManager;

class RobotsTxtController extends Controller
{
    public function __construct(
        protected RobotsTxtManager $robotsTxtManager
    ) {}

    public function __invoke(): Response
    {
        $content = $this->robotsTxtManager->render();

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
