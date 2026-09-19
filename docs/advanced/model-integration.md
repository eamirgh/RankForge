# Eloquent Model Integration

RankForge seamlessly extracts SEO metadata directly from Eloquent models using the `forModel()` method and the `HasJsonLd` trait.

---

## 1. Automatic Fallback Hierarchy

When you call `RankForge::forModel($model)`, RankForge inspects the model using an intelligent fallback order:

```
1. View / Explicit Setter Override
   └── 2. Model Specific Method (e.g. getSeoTitle())
       └── 3. Model Attribute (e.g. title, name)
           └── 4. Global Configuration Default (config/rankforge.php)
```

### Supported Model Methods & Attributes

| SEO Property | Methods Checked | Attributes Checked |
|---|---|---|
| **Title** | `getSeoTitle()` | `seo_title`, `title`, `name` |
| **Description** | `getSeoDescription()` | `seo_description`, `description`, `excerpt`, `summary` |
| **Image** | `getSeoImage()` | `seo_image`, `image`, `featured_image`, `cover_image` |
| **Canonical** | `getSeoCanonical()` | `canonical_url`, `url` |

---

## 2. Example Eloquent Model

```php
namespace App\Models;

use Eamirgh\RankForge\Schema\Concerns\HasJsonLd;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasJsonLd;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'published_at',
    ];

    /**
     * Custom SEO Title (prioritized over $this->title).
     */
    public function getSeoTitle(): string
    {
        return $this->meta_title ?? $this->title;
    }

    /**
     * Custom SEO Image (prioritized over $this->featured_image).
     */
    public function getSeoImage(): string
    {
        return asset('storage/' . $this->featured_image);
    }
}
```

---

## 3. The `HasJsonLd` Trait

By including `use HasJsonLd;` on your model, RankForge automatically generates a Schema.org `Article` schema whenever you call `RankForge::forModel($article)`.

### Customizing the Model's JSON-LD Schema
You can override `toJsonLd()` on any model to return any Schema.org type (e.g., `Product`, `FAQPage`, `HowTo`):

```php
namespace App\Models;

use Eamirgh\RankForge\Schema\Concerns\HasJsonLd;
use Eamirgh\RankForge\Schema\Types\Product;
use Eamirgh\RankForge\Schema\Types\Offer;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasJsonLd;

    public function toJsonLd(): Product
    {
        return Product::make()
            ->name($this->title)
            ->description($this->description)
            ->image(asset($this->thumbnail))
            ->offers(
                Offer::make()
                    ->price($this->price)
                    ->priceCurrency('USD')
                    ->availability('https://schema.org/InStock')
            )
            ->aggregateRating(
                ratingValue: $this->average_rating,
                reviewCount: $this->reviews_count
            );
    }
}
```
