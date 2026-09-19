<?php

namespace Eamirgh\RankForge\Schema\Concerns;

use Eamirgh\RankForge\Schema\Graph;
use Eamirgh\RankForge\Schema\Types\AbstractType;
use Eamirgh\RankForge\Schema\Types\Article;

trait HasJsonLd
{
    /**
     * Default JSON-LD schema generation for the model.
     * Can be overridden by the model.
     */
    public function toJsonLd(): AbstractType|Graph|array
    {
        $schema = new Article();

        $headline = method_exists($this, 'getSeoTitle') ? $this->getSeoTitle() : null;
        $headline ??= $this->seo_title ?? $this->title ?? $this->name ?? $this->headline ?? null;

        if ($headline !== null) {
            $schema->headline((string) $headline);
        }

        $description = method_exists($this, 'getSeoDescription') ? $this->getSeoDescription() : null;
        $description ??= $this->seo_description ?? $this->description ?? $this->excerpt ?? $this->summary ?? null;

        if ($description !== null) {
            $schema->description((string) $description);
        }

        $image = method_exists($this, 'getSeoImage') ? $this->getSeoImage() : null;
        $image ??= $this->seo_image ?? $this->featured_image ?? $this->image ?? $this->cover_image ?? null;

        if ($image !== null) {
            $schema->image((string) $image);
        }

        if (isset($this->created_at)) {
            $schema->datePublished($this->created_at);
        }

        if (isset($this->updated_at)) {
            $schema->dateModified($this->updated_at);
        }

        $url = null;
        if (method_exists($this, 'getCanonicalUrl')) {
            $url = $this->getCanonicalUrl();
        } elseif (method_exists($this, 'url')) {
            $url = $this->url();
        } elseif (isset($this->canonical_url)) {
            $url = $this->canonical_url;
        } elseif (isset($this->url)) {
            $url = $this->url;
        }

        if ($url !== null) {
            $schema->mainEntityOfPage((string) $url);
        }

        return $schema;
    }
}
