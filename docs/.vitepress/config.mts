import { defineConfig } from 'vitepress';

export default defineConfig({
  title: 'RankForge',
  description: 'Enterprise-grade SEO & GEO Engine for Laravel 11, 12, and 13',
  lang: 'en-US',
  lastUpdated: true,
  cleanUrls: true,

  head: [
    ['link', { rel: 'icon', type: 'image/svg+xml', href: '/logo.svg' }],
    ['meta', { name: 'theme-color', content: '#3eaf7c' }],
  ],

  themeConfig: {
    logo: '/logo.svg',
    siteTitle: 'RankForge',

    nav: [
      { text: 'Guide', link: '/guide/getting-started' },
      { text: 'Meta Tags', link: '/guide/dynamic-meta-tags' },
      { text: 'JSON-LD', link: '/guide/json-ld-schemas' },
      { text: 'Sitemaps', link: '/guide/xml-sitemaps' },
      { text: 'LLMs.txt & GEO', link: '/guide/crawlers-and-llms-txt' },
      {
        text: 'v1.x',
        items: [
          { text: 'Changelog', link: 'https://github.com/eamirgh/rankforge/releases' },
          { text: 'Contributing', link: 'https://github.com/eamirgh/rankforge' },
        ],
      },
    ],

    sidebar: [
      {
        text: 'Getting Started',
        items: [
          { text: 'Introduction', link: '/guide/getting-started' },
          { text: 'Configuration', link: '/guide/configuration' },
          { text: 'Artisan Commands', link: '/guide/artisan-commands' },
        ],
      },
      {
        text: 'Core Features',
        items: [
          { text: 'Dynamic Meta Tags', link: '/guide/dynamic-meta-tags' },
          { text: 'JSON-LD Structured Data', link: '/guide/json-ld-schemas' },
          { text: 'XML Sitemaps & IndexNow', link: '/guide/xml-sitemaps' },
          { text: 'Robots.txt & LLMs.txt (GEO)', link: '/guide/crawlers-and-llms-txt' },
        ],
      },
      {
        text: 'Advanced Usage',
        items: [
          { text: 'Headless & Inertia.js', link: '/advanced/headless-and-inertia' },
          { text: 'Eloquent Model Integration', link: '/advanced/model-integration' },
        ],
      },
    ],

    socialLinks: [
      { icon: 'github', link: 'https://github.com/eamirgh/rankforge' },
    ],

    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Copyright © 2026 Amir Ghaffari',
    },

    search: {
      provider: 'local',
    },
  },
});
