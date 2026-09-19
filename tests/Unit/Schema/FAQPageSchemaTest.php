<?php

namespace Eamirgh\RankForge\Tests\Unit\Schema;

use Eamirgh\RankForge\Schema\Types\FAQPage;
use Eamirgh\RankForge\Tests\TestCase;

class FAQPageSchemaTest extends TestCase
{
    public function test_it_generates_faq_page_schema(): void
    {
        $faq = FAQPage::make()
            ->addQuestion('What is RankForge?', 'An SEO engine for Laravel.')
            ->questions([
                ['question' => 'Does it support PHP 8.2+?', 'answer' => 'Yes, fully.'],
            ]);

        $array = $faq->toArray();

        $this->assertEquals('https://schema.org', $array['@context']);
        $this->assertEquals('FAQPage', $array['@type']);
        $this->assertCount(2, $array['mainEntity']);
        $this->assertEquals('Question', $array['mainEntity'][0]['@type']);
        $this->assertEquals('What is RankForge?', $array['mainEntity'][0]['name']);
        $this->assertEquals('Answer', $array['mainEntity'][0]['acceptedAnswer']['@type']);
        $this->assertEquals('An SEO engine for Laravel.', $array['mainEntity'][0]['acceptedAnswer']['text']);
        $this->assertEquals('Does it support PHP 8.2+?', $array['mainEntity'][1]['name']);
        $this->assertEquals('Yes, fully.', $array['mainEntity'][1]['acceptedAnswer']['text']);
    }

    public function test_it_adds_questions_from_associative_array(): void
    {
        $faq = FAQPage::make()->questions([
            'Question 1' => 'Answer 1',
            'Question 2' => 'Answer 2',
        ]);

        $array = $faq->toArray();
        $this->assertCount(2, $array['mainEntity']);
        $this->assertEquals('Question 1', $array['mainEntity'][0]['name']);
        $this->assertEquals('Answer 1', $array['mainEntity'][0]['acceptedAnswer']['text']);
    }
}
