# Headless & Inertia.js Integration

RankForge is designed with first-class support for headless Laravel architectures, single-page applications (SPAs), and Inertia.js (Vue 3 / React).

---

## Inertia.js (Vue 3)

### 1. Share SEO Data in Middleware
In your `HandleInertiaRequests` middleware:

```php
namespace App\Http\Middleware;

use Eamirgh\RankForge\Facades\RankForge;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'seo' => fn () => RankForge::toArray(),
        ]);
    }
}
```

### 2. Render with Inertia `<Head>` in Vue 3
In your root layout or page component (e.g., `resources/js/Layouts/AppLayout.vue`):

```vue
<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const seo = computed(() => page.props.seo);
</script>

<template>
  <div>
    <Head>
      <!-- Title & Basic Meta -->
      <title>{{ seo.title }}</title>
      <meta name="description" :content="seo.description" />
      <meta name="robots" :content="seo.robots" />
      <link v-if="seo.canonical" rel="canonical" :href="seo.canonical" />

      <!-- Keywords -->
      <meta v-if="seo.keywords?.length" name="keywords" :content="seo.keywords.join(', ')" />

      <!-- Open Graph -->
      <template v-if="seo.open_graph">
        <meta v-for="(val, key) in seo.open_graph" :key="key" :property="`og:${key}`" :content="val" />
      </template>

      <!-- Twitter Card -->
      <template v-if="seo.twitter">
        <meta v-for="(val, key) in seo.twitter" :key="key" :name="`twitter:${key}`" :content="val" />
      </template>

      <!-- Hreflang Alternates -->
      <template v-if="seo.hreflang">
        <link v-for="(href, lang) in seo.hreflang" :key="lang" rel="alternate" :hreflang="lang" :href="href" />
      </template>

      <!-- JSON-LD Structured Data -->
      <component
        :is="'script'"
        v-for="(schema, idx) in seo.json_ld"
        :key="idx"
        type="application/ld+json"
        v-html="JSON.stringify(schema)"
      />
    </Head>

    <slot />
  </div>
</template>
```

---

## Inertia.js (React)

In your root layout or page component (e.g., `resources/js/Layouts/AppLayout.tsx`):

```tsx
import { Head, usePage } from '@inertiajs/react';
import React from 'react';

export default function AppLayout({ children }: { children: React.ReactNode }) {
  const { seo } = usePage<{ seo: any }>().props;

  return (
    <>
      <Head>
        <title>{seo.title}</title>
        <meta name="description" content={seo.description} />
        <meta name="robots" content={seo.robots} />
        {seo.canonical && <link rel="canonical" href={seo.canonical} />}

        {/* Open Graph */}
        {seo.open_graph && Object.entries(seo.open_graph).map(([key, val]) => (
          <meta key={key} property={`og:${key}`} content={String(val)} />
        ))}

        {/* Twitter */}
        {seo.twitter && Object.entries(seo.twitter).map(([key, val]) => (
          <meta key={key} name={`twitter:${key}`} content={String(val)} />
        ))}

        {/* JSON-LD */}
        {seo.json_ld?.map((schema: any, idx: number) => (
          <script
            key={idx}
            type="application/ld+json"
            dangerouslySetInnerHTML={{ __html: JSON.stringify(schema) }}
          />
        ))}
      </Head>

      <main>{children}</main>
    </>
  );
}
```

---

## Headless JSON API Serialization

If you are using Laravel as a pure API backend for Next.js, Nuxt, or Astro:

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Eamirgh\RankForge\Facades\RankForge;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function show(Post $post): JsonResponse
    {
        RankForge::forModel($post);

        return response()->json([
            'data' => $post,
            'seo' => RankForge::toArray(),
        ]);
    }
}
```
