# Model Context Protocol (MCP) Server

RankForge features native, zero-dependency support for Anthropic's **Model Context Protocol (MCP)** via JSON-RPC 2.0 over standard I/O (`stdio`).

This allows AI assistants and coding agents (Claude Desktop, Cursor, Windsurf, Zed, Copilot) to inspect, audit, and interact with your Laravel application's SEO and GEO engine directly.

---

## Starting the MCP Server

Start the RankForge MCP server via Artisan:

```bash
php artisan rankforge:mcp
# or using the alias:
php artisan rankforge:mcp:serve
```

---

## Connecting with AI Clients

### 1. Claude Desktop Configuration

Add RankForge to your `claude_desktop_config.json`:

#### macOS
`~/Library/Application Support/Claude/claude_desktop_config.json`

#### Windows
`%APPDATA%\Claude\claude_desktop_config.json`

```json
{
  "mcpServers": {
    "rankforge": {
      "command": "php",
      "args": [
        "/path/to/your/laravel-app/artisan",
        "rankforge:mcp"
      ]
    }
  }
}
```

### 2. Cursor IDE Configuration

In `.cursor/mcp.json` or Cursor Settings -> MCP:

```json
{
  "mcpServers": {
    "rankforge": {
      "command": "php",
      "args": ["artisan", "rankforge:mcp"]
    }
  }
}
```

---

## Available MCP Tools

AI agents can execute the following tools via RankForge:

| Tool | Description | Parameters |
|---|---|---|
| `check_seo_health` | Runs an automated SEO audit checking site title, description, robots directives, sitemaps, and IndexNow configuration. | None |
| `get_llms_txt` | Generates and returns semantic Markdown documentation for AI crawlers (`/llms.txt` or `/llms-full.txt`). | `full` (boolean, optional) |
| `get_robots_txt` | Returns the active `robots.txt` configuration, including environment locks and bot directives. | None |
| `get_sitemap` | Returns the root XML sitemap index or a specific chunked sitemap section. | `section` (string, optional), `page` (int, optional) |
| `inspect_html_meta` | Parses and audits SEO metadata (title, description, canonical, robots, OG, Twitter) from raw HTML content. | `html` (string, required) |
| `submit_indexnow` | Submits URL(s) to the IndexNow protocol for instant indexing across Bing and Yandex. | `urls` (array of strings, required) |

---

## Available MCP Resources

RankForge exposes live context resources to LLMs:

| Resource URI | MIME Type | Description |
|---|---|---|
| `rankforge://llms.txt` | `text/markdown` | Concise semantic Markdown summary of site documentation |
| `rankforge://llms-full.txt` | `text/markdown` | Comprehensive unpaginated documentation bundle for LLM context windows |
| `rankforge://robots.txt` | `text/plain` | The active `robots.txt` directives |
| `rankforge://sitemap.xml` | `application/xml` | The root XML sitemap index |

---

## Example Agent Interactions

When connected via MCP, you can prompt your AI assistant:

> *"Audit my Laravel app's SEO health and check if our sitemap and robots.txt are properly configured."*

The AI assistant will invoke `check_seo_health` and `get_robots_txt` via MCP and provide a detailed analysis.

> *"Inspect the SEO meta tags of this rendered page HTML to ensure Open Graph and Twitter Card tags are complete."*

The AI assistant will invoke `inspect_html_meta` with the HTML payload and verify all meta properties.
