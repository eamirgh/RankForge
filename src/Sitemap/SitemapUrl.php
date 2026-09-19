<?php

namespace RankForge\Sitemap;

use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use InvalidArgumentException;
use JsonSerializable;

class SitemapUrl implements Arrayable, Jsonable, JsonSerializable
{
    protected const VALID_CHANGEFREQS = [
        'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never',
    ];

    protected string $loc;

    protected DateTimeInterface|string|null $lastmod = null;

    protected ?string $changefreq = null;

    protected ?float $priority = null;

    /** @var array<array{loc: string, title?: string|null, caption?: string|null}> */
    protected array $images = [];

    /** @var array<array{thumbnail_loc: string, title: string, description: string, content_loc?: string|null, player_loc?: string|null}> */
    protected array $videos = [];

    /** @var array{publication_name: string, publication_language: string, publication_date: string, title: string}|null */
    protected ?array $news = null;

    /** @var array<array{hreflang: string, href: string}> */
    protected array $alternates = [];

    public function __construct(string $loc)
    {
        $this->loc = $loc;
    }

    public static function make(string $loc): static
    {
        return new static($loc);
    }

    public function loc(string $loc): static
    {
        $this->loc = $loc;

        return $this;
    }

    public function lastmod(DateTimeInterface|string|null $lastmod): static
    {
        $this->lastmod = $lastmod;

        return $this;
    }

    public function changefreq(?string $changefreq): static
    {
        if ($changefreq !== null) {
            $normalized = strtolower(trim($changefreq));
            if (! in_array($normalized, self::VALID_CHANGEFREQS, true)) {
                throw new InvalidArgumentException("Invalid changefreq: {$changefreq}");
            }
            $this->changefreq = $normalized;
        } else {
            $this->changefreq = null;
        }

        return $this;
    }

    public function priority(?float $priority): static
    {
        if ($priority !== null) {
            if ($priority < 0.0 || $priority > 1.0) {
                throw new InvalidArgumentException("Priority must be between 0.0 and 1.0");
            }
            $this->priority = round($priority, 1);
        } else {
            $this->priority = null;
        }

        return $this;
    }

    public function image(string $loc, ?string $title = null, ?string $caption = null): static
    {
        $this->images[] = array_filter([
            'loc' => $loc,
            'title' => $title,
            'caption' => $caption,
        ], fn ($val) => $val !== null);

        return $this;
    }

    /**
     * @param array{loc: string, title?: string|null, caption?: string|null} $image
     */
    public function addImage(array $image): static
    {
        $this->images[] = $image;

        return $this;
    }

    public function video(
        string $thumbnailLoc,
        string $title,
        string $description,
        ?string $contentLoc = null,
        ?string $playerLoc = null,
    ): static {
        $this->videos[] = array_filter([
            'thumbnail_loc' => $thumbnailLoc,
            'title' => $title,
            'description' => $description,
            'content_loc' => $contentLoc,
            'player_loc' => $playerLoc,
        ], fn ($val) => $val !== null);

        return $this;
    }

    /**
     * @param array{thumbnail_loc: string, title: string, description: string, content_loc?: string|null, player_loc?: string|null} $video
     */
    public function addVideo(array $video): static
    {
        $this->videos[] = $video;

        return $this;
    }

    public function news(
        string $publicationName,
        string $publicationLanguage,
        DateTimeInterface|string $publicationDate,
        string $title,
    ): static {
        $date = $publicationDate instanceof DateTimeInterface
            ? $publicationDate->format(DateTimeInterface::ATOM)
            : $publicationDate;

        $this->news = [
            'publication_name' => $publicationName,
            'publication_language' => $publicationLanguage,
            'publication_date' => $date,
            'title' => $title,
        ];

        return $this;
    }

    public function alternate(string $hreflang, string $href): static
    {
        $this->alternates[] = [
            'hreflang' => $hreflang,
            'href' => $href,
        ];

        return $this;
    }

    /**
     * @param array{hreflang: string, href: string} $alternate
     */
    public function addAlternate(array $alternate): static
    {
        $this->alternates[] = $alternate;

        return $this;
    }

    public function getLoc(): string
    {
        return $this->loc;
    }

    public function getLastmod(): DateTimeInterface|string|null
    {
        return $this->lastmod;
    }

    public function getChangefreq(): ?string
    {
        return $this->changefreq;
    }

    public function getPriority(): ?float
    {
        return $this->priority;
    }

    /**
     * @return array<array{loc: string, title?: string|null, caption?: string|null}>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return array<array{thumbnail_loc: string, title: string, description: string, content_loc?: string|null, player_loc?: string|null}>
     */
    public function getVideos(): array
    {
        return $this->videos;
    }

    /**
     * @return array{publication_name: string, publication_language: string, publication_date: string, title: string}|null
     */
    public function getNews(): ?array
    {
        return $this->news;
    }

    /**
     * @return array<array{hreflang: string, href: string}>
     */
    public function getAlternates(): array
    {
        return $this->alternates;
    }

    public function toXml(): string
    {
        $xml = "  <url>\n";
        $xml .= '    <loc>'.htmlspecialchars($this->loc, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";

        if ($this->lastmod !== null) {
            $formatted = $this->lastmod instanceof DateTimeInterface
                ? $this->lastmod->format(DateTimeInterface::ATOM)
                : $this->lastmod;
            $xml .= '    <lastmod>'.htmlspecialchars($formatted, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</lastmod>\n";
        }

        if ($this->changefreq !== null) {
            $xml .= '    <changefreq>'.htmlspecialchars($this->changefreq, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</changefreq>\n";
        }

        if ($this->priority !== null) {
            $xml .= '    <priority>'.number_format($this->priority, 1, '.', '')."</priority>\n";
        }

        foreach ($this->images as $img) {
            $xml .= "    <image:image>\n";
            $xml .= '      <image:loc>'.htmlspecialchars($img['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:loc>\n";
            if (! empty($img['title'])) {
                $xml .= '      <image:title>'.htmlspecialchars($img['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:title>\n";
            }
            if (! empty($img['caption'])) {
                $xml .= '      <image:caption>'.htmlspecialchars($img['caption'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</image:caption>\n";
            }
            $xml .= "    </image:image>\n";
        }

        foreach ($this->videos as $vid) {
            $xml .= "    <video:video>\n";
            $xml .= '      <video:thumbnail_loc>'.htmlspecialchars($vid['thumbnail_loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</video:thumbnail_loc>\n";
            $xml .= '      <video:title>'.htmlspecialchars($vid['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</video:title>\n";
            $xml .= '      <video:description>'.htmlspecialchars($vid['description'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</video:description>\n";
            if (! empty($vid['content_loc'])) {
                $xml .= '      <video:content_loc>'.htmlspecialchars($vid['content_loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</video:content_loc>\n";
            }
            if (! empty($vid['player_loc'])) {
                $xml .= '      <video:player_loc>'.htmlspecialchars($vid['player_loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</video:player_loc>\n";
            }
            $xml .= "    </video:video>\n";
        }

        if ($this->news !== null) {
            $xml .= "    <news:news>\n";
            $xml .= "      <news:publication>\n";
            $xml .= '        <news:name>'.htmlspecialchars($this->news['publication_name'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</news:name>\n";
            $xml .= '        <news:language>'.htmlspecialchars($this->news['publication_language'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</news:language>\n";
            $xml .= "      </news:publication>\n";
            $xml .= '      <news:publication_date>'.htmlspecialchars($this->news['publication_date'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</news:publication_date>\n";
            $xml .= '      <news:title>'.htmlspecialchars($this->news['title'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</news:title>\n";
            $xml .= "    </news:news>\n";
        }

        foreach ($this->alternates as $alt) {
            $xml .= '    <xhtml:link rel="alternate" hreflang="'.htmlspecialchars($alt['hreflang'], ENT_XML1 | ENT_QUOTES, 'UTF-8').'" href="'.htmlspecialchars($alt['href'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."\"/>\n";
        }

        $xml .= '  </url>';

        return $xml;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['loc' => $this->loc];

        if ($this->lastmod !== null) {
            $data['lastmod'] = $this->lastmod instanceof DateTimeInterface
                ? $this->lastmod->format(DateTimeInterface::ATOM)
                : $this->lastmod;
        }

        if ($this->changefreq !== null) {
            $data['changefreq'] = $this->changefreq;
        }

        if ($this->priority !== null) {
            $data['priority'] = $this->priority;
        }

        if ($this->images !== []) {
            $data['images'] = $this->images;
        }

        if ($this->videos !== []) {
            $data['videos'] = $this->videos;
        }

        if ($this->news !== null) {
            $data['news'] = $this->news;
        }

        if ($this->alternates !== []) {
            $data['alternates'] = $this->alternates;
        }

        return $data;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_UNESCAPED_SLASHES);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
