---
name: wordpress-mcp
description: Connect OpenCode to any WordPress site running Immens MCP Fortress. Manage posts, pages, media, comments, users, WooCommerce, SEO, translations, and 274+ WordPress operations via MCP tools. Secure bearer-token authentication with SHA-256 hashed API keys, IP whitelisting, and rate limiting.
license: MIT
compatibility: opencode
metadata:
  publisher: Studio Immens
  category: wordpress
---
# WordPress MCP for OpenCode

Connect OpenCode to any WordPress site running the Immens MCP Fortress plugin.

## Configuration

Add to your `opencode.json`:

```json
{
  "mcp": {
    "immens-mcp-fortress": {
      "type": "remote",
      "url": "https://YOUR_SITE.com/wp-json/immens-mcp-fortress/v1/mcp",
      "enabled": true,
      "headers": {
        "Authorization": "Bearer YOUR_API_KEY"
      },
      "oauth": false
    }
  }
}
```

Then verify with:

```bash
opencode mcp list
```

## Auto-Discovery

OpenCode will auto-discover Immens MCP Fortress on your WordPress site via the `/.well-known/opencode` endpoint.

## Available Tools (274 total)

### Content
- Posts & Pages: create, read, update, delete, search, count, find/replace
- Media: upload from file or URL, list, update alt text, delete
- Comments: CRUD, approve, spam, trash

### Management
- Users, Categories, Tags, Terms: full CRUD with meta
- Menus: create and manage navigation menus and items
- Blocks: reusable block CRUD, block types
- Templates & Global Styles: get and update

### Plugin Integrations
- WooCommerce: products, orders, customers, stats
- Yoast SEO / RankMath: SEO analysis, sitemap
- Polylang / Loco Translate: translations
- CF7: forms, submissions, stats
- And many more with Pro

## Quick Start

```bash
# 1. Install the plugin on WordPress
# 2. Go to MCP Fortress → Access Points → Add New
# 3. Copy the API key (format: imf_xxxxxxxxxxxx...)
# 4. Add to opencode.json as shown above
# 5. Start using: "List my latest 5 posts" or "Create a new draft page"
```
