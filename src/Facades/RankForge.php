<?php

namespace RankForge\Facades;

use DateTimeInterface;
use Illuminate\Support\Facades\Facade;
use RankForge\RankForgeManager;
use RankForge\Schema\Graph;
use RankForge\Schema\SchemaManager;
use RankForge\Schema\Types\AbstractType;

/**
 * @method static RankForgeManager title(string $title)
 * @method static ?string getTitle()
 * @method static string getRenderedTitle()
 * @method static RankForgeManager description(string $description)
 * @method static string getDescription()
 * @method static RankForgeManager keywords(array|string $keywords)
 * @method static array getKeywords()
 * @method static string getKeywordsString()
 * @method static RankForgeManager canonical(string $url)
 * @method static ?string getCanonicalUrl()
 * @method static RankForgeManager robots(string $robots)
 * @method static RankForgeManager noindex()
 * @method static RankForgeManager nofollow()
 * @method static RankForgeManager index()
 * @method static RankForgeManager follow()
 * @method static RankForgeManager noarchive()
 * @method static RankForgeManager nosnippet()
 * @method static RankForgeManager maxSnippet(int $chars)
 * @method static RankForgeManager maxImagePreview(string $size)
 * @method static RankForgeManager maxVideoPreview(int $seconds)
 * @method static RankForgeManager customRobots(string $name, string $content)
 * @method static array getCustomRobots()
 * @method static string getRobots()
 * @method static RankForgeManager ogTitle(string $title)
 * @method static RankForgeManager ogDescription(string $description)
 * @method static RankForgeManager ogImage(string $url, ?int $width = null, ?int $height = null, ?string $alt = null, ?string $type = null, ?string $secureUrl = null)
 * @method static RankForgeManager ogImageAlt(string $alt)
 * @method static RankForgeManager ogImageType(string $type)
 * @method static RankForgeManager ogImageSecureUrl(string $url)
 * @method static RankForgeManager ogLocaleAlternate(string|array $locales)
 * @method static RankForgeManager ogType(string $type)
 * @method static RankForgeManager articlePublishedTime(string|DateTimeInterface $time)
 * @method static RankForgeManager articleModifiedTime(string|DateTimeInterface $time)
 * @method static RankForgeManager articleAuthor(string|array $author)
 * @method static RankForgeManager articleSection(string $section)
 * @method static RankForgeManager articleTags(array|string $tags)
 * @method static array getOpenGraph()
 * @method static RankForgeManager twitterCard(string $card)
 * @method static RankForgeManager twitterTitle(string $title)
 * @method static RankForgeManager twitterDescription(string $description)
 * @method static RankForgeManager twitterImage(string $url, ?string $alt = null)
 * @method static RankForgeManager twitterImageAlt(string $alt)
 * @method static RankForgeManager twitterSite(string $site)
 * @method static RankForgeManager twitterCreator(string $creator)
 * @method static array getTwitter()
 * @method static RankForgeManager forModel(mixed $model)
 * @method static RankForgeManager xDefault(string $url)
 * @method static RankForgeManager hreflang(string $locale, string $url)
 * @method static RankForgeManager hreflangs(array $locales)
 * @method static array getHreflangEntries()
 * @method static bool isHreflangEnabled()
 * @method static RankForgeManager jsonLd(AbstractType|Graph $schema)
 * @method static RankForgeManager schema(AbstractType|Graph $schema)
 * @method static Graph graph(?Graph $graph = null)
 * @method static SchemaManager getSchemaManager()
 * @method static array getJsonLdSchemas()
 * @method static bool isJsonLdEnabled()
 * @method static bool isJsonLdPrettyPrint()
 * @method static string renderHead()
 * @method static array toArray()
 * @method static string toJson()
 * @method static mixed configGet(string $key, mixed $default = null)
 * @method static array getConfig()
 * @method static \RankForge\Sitemap\SitemapManager sitemap()
 * @method static \RankForge\Crawlers\RobotsTxtManager robotsTxt()
 * @method static \RankForge\Crawlers\LlmsTxtManager llmsTxt()
 * @method static \RankForge\Sitemap\IndexNow indexNow()
 *
 * @see \RankForge\RankForgeManager
 */
class RankForge extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return RankForgeManager::class;
    }
}
