# Artisan Commands

RankForge includes a dedicated suite of Artisan commands for automation, CI/CD pipelines, and scheduled tasks.

---

## `rankforge:install`

Publishes the package configuration file and view templates:

```bash
php artisan rankforge:install
```

---

## `rankforge:sitemap:generate`

Pre-renders static XML sitemap files to public disk storage:

```bash
php artisan rankforge:sitemap:generate
```

### Options
| Option | Description | Default |
|---|---|---|
| `--disk` | Storage disk (e.g., `public`, `s3`) | `public` |
| `--path` | Output directory path within the disk | `sitemaps` |

### Example
```bash
php artisan rankforge:sitemap:generate --disk=public --path=sitemaps
```

---

## `rankforge:sitemap:ping`

Submits updated sitemaps to search engines (Bing) and triggers the IndexNow protocol:

```bash
php artisan rankforge:sitemap:ping
```

### Options
| Option | Description | Default |
|---|---|---|
| `--url` | Specific sitemap URL to submit | `url('sitemap.xml')` |

### Example
```bash
php artisan rankforge:sitemap:ping --url=https://example.com/sitemap.xml
```

---

## `rankforge:llms:generate`

Compiles and writes static `llms.txt` and `llms-full.txt` files into the application's `public/` directory:

```bash
php artisan rankforge:llms:generate
```

---

## `rankforge:check`

Performs an automated SEO health check auditing your configuration, robots directives, sitemaps, and structured data:

```bash
php artisan rankforge:check
```

### Sample Output
```text
RankForge SEO Health Check

+-------------------+--------+-------------------------------------------------------+
| Check             | Status | Details                                               |
+-------------------+--------+-------------------------------------------------------+
| Site Name         | OK     | RankForge Enterprise                                  |
| Default Title     | OK     | RankForge Enterprise                                  |
| Default Desc      | OK     | 85 characters configured                              |
| robots.txt        | OK     | Enabled (dynamic: yes)                                |
| Sitemaps          | OK     | Enabled (1 source registered)                         |
| IndexNow          | OK     | Key configured                                        |
| JSON-LD Schema    | OK     | WebSite schema validates correctly                    |
+-------------------+--------+-------------------------------------------------------+
```
