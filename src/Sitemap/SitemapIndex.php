<?php

namespace RankForge\Sitemap;

use DateTimeInterface;

class SitemapIndex
{
    /** @var array<array{loc: string, lastmod: DateTimeInterface|string|null}> */
    protected array $sitemaps = [];

    public static function make(): static
    {
        return new static();
    }

    public function addSitemap(string $loc, DateTimeInterface|string|null $lastmod = null): static
    {
        $this->sitemaps[] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
        ];

        return $this;
    }

    /**
     * @return array<array{loc: string, lastmod: DateTimeInterface|string|null}>
     */
    public function getSitemaps(): array
    {
        return $this->sitemaps;
    }

    public function toXml(): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($this->sitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= '    <loc>'.htmlspecialchars($sitemap['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";

            if ($sitemap['lastmod'] !== null) {
                $lastmod = $sitemap['lastmod'] instanceof DateTimeInterface
                    ? $sitemap['lastmod']->format(DateTimeInterface::ATOM)
                    : $sitemap['lastmod'];
                $xml .= '    <lastmod>'.htmlspecialchars($lastmod, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
            }

            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }
}
