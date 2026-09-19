<?php

use Illuminate\Support\Facades\Route;
use Eamirgh\RankForge\Http\Controllers\LlmsTxtController;
use Eamirgh\RankForge\Http\Controllers\RobotsTxtController;
use Eamirgh\RankForge\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| RankForge Web Routes
|--------------------------------------------------------------------------
*/

if (config('rankforge.robots_txt.enabled', true) && config('rankforge.robots_txt.dynamic', true)) {
    Route::get('robots.txt', RobotsTxtController::class)->name('rankforge.robots');
}

if (config('rankforge.sitemap.enabled', true)) {
    Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('rankforge.sitemap.index');
    Route::get('sitemap-{section}-{page}.xml', [SitemapController::class, 'show'])
        ->where(['section' => '[a-zA-Z0-9\-_]+', 'page' => '[0-9]+'])
        ->name('rankforge.sitemap.show');
}

if (config('rankforge.llms_txt.enabled', true)) {
    Route::get('llms.txt', [LlmsTxtController::class, 'index'])->name('rankforge.llms');
    Route::get('llms-full.txt', [LlmsTxtController::class, 'full'])->name('rankforge.llms.full');
}
