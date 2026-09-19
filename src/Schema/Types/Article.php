<?php

namespace Eamirgh\RankForge\Schema\Types;

use DateTimeInterface;

class Article extends AbstractType
{
    public function __construct(string $type = 'Article')
    {
        parent::__construct($type);
    }

    public function headline(string $headline): static
    {
        return $this->setProperty('headline', $headline);
    }

    public function description(string $description): static
    {
        return $this->setProperty('description', $description);
    }

    /**
     * @param  string|string[]  $image
     */
    public function image(string|array $image): static
    {
        return $this->setProperty('image', $image);
    }

    public function datePublished(string|DateTimeInterface $date): static
    {
        return $this->setProperty('datePublished', $date);
    }

    public function dateModified(string|DateTimeInterface $date): static
    {
        return $this->setProperty('dateModified', $date);
    }

    /**
     * @param  string|string[]|Organization|AbstractType  $author
     */
    public function author(string|array|Organization|AbstractType $author): static
    {
        if (is_string($author)) {
            $author = [
                '@type' => 'Person',
                'name' => $author,
            ];
        }

        return $this->setProperty('author', $author);
    }

    /**
     * @param  string|array<string, mixed>|Organization  $publisher
     */
    public function publisher(string|array|Organization $publisher, ?string $logo = null): static
    {
        if (is_string($publisher)) {
            $pubData = [
                '@type' => 'Organization',
                'name' => $publisher,
            ];

            if ($logo !== null) {
                $pubData['logo'] = [
                    '@type' => 'ImageObject',
                    'url' => $logo,
                ];
            }

            $publisher = $pubData;
        } elseif ($publisher instanceof Organization && $logo !== null) {
            $publisher->logo($logo);
        }

        return $this->setProperty('publisher', $publisher);
    }

    public function mainEntityOfPage(string $url): static
    {
        return $this->setProperty('mainEntityOfPage', [
            '@type' => 'WebPage',
            '@id' => $url,
        ]);
    }
}
