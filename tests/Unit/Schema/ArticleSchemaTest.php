<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use DateTimeImmutable;
use Eamirgh\RankForge\Schema\Types\Article;
use Eamirgh\RankForge\Schema\Types\BlogPosting;
use Eamirgh\RankForge\Schema\Types\NewsArticle;
use Eamirgh\RankForge\Schema\Types\Organization;
use Eamirgh\RankForge\Tests\TestCase;

class ArticleSchemaTest extends TestCase
{
    public function test_it_generates_article_schema_with_all_properties(): void
    {
        $date = new DateTimeImmutable('2025-02-01T10:00:00Z');

        $article = Article::make()
            ->headline('Breaking News')
            ->description('An insightful article')
            ->image(['https://example.com/art1.jpg'])
            ->datePublished($date)
            ->dateModified($date)
            ->author('John Doe')
            ->publisher('The Daily News', 'https://example.com/logo.png')
            ->mainEntityOfPage('https://example.com/news/1');

        $array = $article->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('Article', $array['@type']);
        $this->assertEquals('Breaking News', $array['headline']);
        $this->assertEquals('An insightful article', $array['description']);
        $this->assertEquals(['https://example.com/art1.jpg'], $array['image']);
        $this->assertEquals('Person', $array['author']['@type']);
        $this->assertEquals('John Doe', $array['author']['name']);
        $this->assertEquals('Organization', $array['publisher']['@type']);
        $this->assertEquals('https://example.com/logo.png', $array['publisher']['logo']['url']);
        $this->assertEquals('WebPage', $array['mainEntityOfPage']['@type']);
        $this->assertEquals('https://example.com/news/1', $array['mainEntityOfPage']['@id']);
    }

    public function test_it_supports_author_as_organization(): void
    {
        $org = Organization::make()->name('Tech Corp')->url('https://techcorp.com');

        $article = Article::make()
            ->headline('Tech Post')
            ->author($org);

        $array = $article->toArray();
        $this->assertEquals('Organization', $array['author']['@type']);
        $this->assertEquals('Tech Corp', $array['author']['name']);
    }

    public function test_it_supports_blog_posting_and_news_article_subtypes(): void
    {
        $blog = BlogPosting::make()->headline('Blog post');
        $this->assertEquals('BlogPosting', $blog->getType());

        $news = NewsArticle::make()->headline('News post');
        $this->assertEquals('NewsArticle', $news->getType());
    }
}
