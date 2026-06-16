# Immens MCP Fortress

**Military-grade MCP Server for WordPress** — Turn any WordPress site into a secure, multi-access-point MCP server. Connect Claude, ChatGPT, Cursor, OpenCode, OpenClaw, Windsurf, Cline and any MCP-compatible AI agent to manage your site.

[![Version](https://img.shields.io/badge/version-1.1.0-blue.svg)](https://studioimmens.com)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-brightgreen.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-GPL--2.0-or-later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![MCP](https://img.shields.io/badge/MCP-2025--11--25-orange.svg)](https://modelcontextprotocol.io)

---

## What It Does

Immens MCP Fortress exposes your entire WordPress site as an MCP (Model Context Protocol) server. AI agents can read, create, update, and delete content through natural conversation — with military-grade security at every layer.

### How It Works

```
┌─────────────────────┐       HTTP/SSE        ┌──────────────────┐
│  AI Agent            │ ────────────────────→ │  WordPress Site   │
│  (Claude/ChatGPT/...)│ ←──────────────────── │  + MCP Fortress   │
└─────────────────────┘    JSON-RPC 2.0        └──────────────────┘
```

## Key Features

### Security First
- **SHA-256 hashed API keys** — keys never stored in plain text
- **IP whitelisting** — IPv4/IPv6 CIDR support per access point
- **Rate limiting** — configurable requests/minute per access point
- **Full audit log** — every tool call recorded with status, timing, IP
- **OAuth 2.0/2.1** — PKCE S256 + Dynamic Client Registration

### Multi-Access-Point Architecture
- Create independent access points, each with its own API key
- Per-access-point tool permissions (read/write per category)
- Enable/disable access points anytime without reconfiguring clients
- **Free tier: 2 access points** | Pro: unlimited

### 121 Free MCP Tools

| Category | Tools | Operations |
|----------|-------|------------|
| Posts | 8 | List, Get, Create, Update, Delete, Count, Full Get, Find/Replace |
| Pages | 8 | List, Get, Create (single + batch), Update (single + batch), Delete, Count |
| Media | 7 | List, Get, Upload (file + URL), Update, Delete, Count |
| Comments | 8 | List, Get, Create, Update, Delete, Approve, Spam, Trash |
| Users | 5 | List, Get, Create, Update, Delete |
| Categories | 5 | List, Get, Create, Update, Delete |
| Tags | 5 | List, Get, Create, Update, Delete |
| Terms | 8 | List, Get, Create, Update, Delete, Get Meta, Update Meta, Delete Meta |
| Menus | 9 | List Menus, Get, Create, Update, Delete + Menu Items CRUD |
| Blocks | 6 | List, Get, Create, Update, Delete, Get Block Types |
| Templates | 3 | List, Get, Update |
| Styles | 2 | Get, Update Global Styles |
| Site | 2 | Get, Update Site Settings |
| Meta | 4 | Get/Update/Delete Post Meta, Add Post Terms |
| Revisions | 3 | List, Get, Restore |
| Search | 1 | Search across all content types |
| Plugins | 1 | List installed plugins |
| Themes | 1 | List installed themes |
| CPT | 1 | List custom post type items |
| WooCommerce | 10 | Products, Orders, Customers, Categories, Store Stats |
| Yoast SEO | 6 | Post SEO, Settings, Sitemap, Social, Schema |
| RankMath SEO | 6 | Analytics, Post SEO, Settings, Sitemap, Social |
| Loco Translate | 5 | Projects, Translations, Export |
| CF7 | 4 | Forms, Stats, Submissions |
| Polylang | 6 | Languages, Translations, Settings |
| Code Snippets | 6 | List, Get, Create, Update, Delete, Toggle |
| W3 Total Cache | 3 | Settings, Flush All, Flush Specific |

### Pro Features (153 additional tools)
- **Gutenberg Block-Level Editing** — parse, add, remove, reorder, update blocks inside posts
- **ClassyBlocks Pro** — 14 animation tools
- **Greenshift** — 16 tools (settings, stylebook, block manager, AI)
- **Stackable** — 8 tools (settings, global colors, typography)
- **Primary Source** — 17 AI content management tools
- **Immens CRM** — 31 contact, pipeline, tag, automation tools
- **Immens Integration** — 22 business management tools
- **The SEO Framework** — 12 SEO analysis and bulk tools
- **TranslatePress** — 10 translation management tools
- **Elementor** — 10 template and page manipulation tools
- **ACF** — 8 field group and field management tools
- **Unlimited Access Points** — remove the 2-AP limit
- **SSE Transport** — for legacy MCP clients
- **Change History** — before/after snapshots of every AI-originated write

## Quick Start

### 1. Install
```bash
# Upload to WordPress plugins directory, or install via admin panel
```

### 2. Create an Access Point
1. Go to **MCP Fortress → Access Points** in WordPress admin
2. Click **Add New Access Point**
3. Name it (e.g., "Claude Desktop")
4. Select tool permissions per category
5. **Copy the API key** (format: `imf_xxxxxxxxxxxx...`)

### 3. Connect Your AI Client

#### Claude Desktop
1. Settings → Connectors → Add custom connector
2. Paste the MCP endpoint URL
3. Enter your API key when prompted

#### OpenCode
Add to your `opencode.json`:
```json
{
  "mcp": {
    "immens-mcp-fortress": {
      "type": "remote",
      "url": "https://yoursite.com/wp-json/immens-mcp-fortress/v1/mcp",
      "enabled": true,
      "headers": {
        "Authorization": "Bearer YOUR_API_KEY"
      }
    }
  }
}
```

#### OpenClaw
```bash
openclaw mcp add immens-mcp-fortress \
  --url "https://yoursite.com/wp-json/immens-mcp-fortress/v1/mcp" \
  --header "Authorization: Bearer YOUR_API_KEY"
```
Or install via ClawHub:
```bash
openclaw skills install wordpress-mcp
```

#### Cursor / Windsurf / Cline
- Transport: `streamable-http`
- URL: `https://yoursite.com/wp-json/immens-mcp-fortress/v1/mcp`
- Headers: `Authorization: Bearer YOUR_API_KEY`

### 4. Verify
The agent will see all enabled tools. Try: *"List my latest 5 posts"*

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                     MCP Server                           │
│                                                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │Access Pts│  │   Auth   │  │  OAuth   │              │
│  │ (API Key)│  │(Bearer)  │  │(PKCE)    │              │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘              │
│       │              │             │                     │
│  ┌────▼──────────────▼─────────────▼──────┐             │
│  │         Permission Guard                │             │
│  └─────────────────┬──────────────────────┘             │
│                    │                                     │
│  ┌─────────────────▼──────────────────────┐             │
│  │          Tool Registry                   │             │
│  │  (Auto-discovered via namespace scan)    │             │
│  └──┬──────────┬──────────┬───────────────┘             │
│     │          │          │                              │
│  ┌──▼──┐  ┌───▼───┐  ┌──▼────┐                          │
│  │Posts│  │Media  │  │ ...   │  (121+ tools)            │
│  └─────┘  └───────┘  └───────┘                          │
│                                                         │
│  ┌──────────────────────────────────────┐               │
│  │         Audit Log                     │               │
│  └──────────────────────────────────────┘               │
└─────────────────────────────────────────────────────────┘
```

### Transports
- **Streamable HTTP** (MCP 2025-11-25) — direct JSON-RPC via HTTP POST
- **SSE** (Server-Sent Events) — for streaming and legacy clients

### Protocol Version Compatibility
MCP protocol versions supported: `2025-11-25`, `2025-06-18`, `2025-03-26`

### Authentication Methods
| Method | Use Case |
|--------|----------|
| Bearer token (API Key) | Direct AI client connections |
| Inline URL parameter | `?api_key=...` for clients without header support |
| OAuth 2.1 PKCE | One-click connections with consent screen |

### OAuth 2.0/2.1 Endpoints
| Endpoint | Purpose |
|----------|---------|
| `/.well-known/oauth-authorization-server` | Authorization server metadata |
| `/.well-known/oauth-protected-resource` | Protected resource metadata |
| `/.well-known/openid-configuration` | OpenID Connect discovery |

### Auto-Discovery
| Endpoint | Client |
|----------|--------|
| `/.well-known/opencode` | OpenCode CLI |
| `/.well-known/openclaw` | OpenClaw Gateway |

## Installation

### Requirements
- WordPress 6.0+
- PHP 7.4+
- HTTPS recommended (required for OAuth production use)

### Install
1. Download the plugin zip or clone this repository
2. Upload to `/wp-content/plugins/immens-mcp-fortress/`
3. Activate in WordPress admin
4. Go to **MCP Fortress** → configure access points

## Security Model

- **API key storage**: `SHA-256(imf_RANDOM64)` — keys are never stored as plaintext
- **Key format**: `imf_` + 64 hex characters (67 characters total)
- **Key display**: shown once at creation, never retrievable
- **IP whitelist**: CIDR notation, supports IPv4 and IPv6
- **Rate limit**: configurable requests per minute, per access point
- **Audit log**: every MCP call logged with tool name, status, timing, auth source
- **OAuth PKCE**: S256 code challenge method, dynamic client registration (RFC 7591)

## Installation via Plugin Directory

Search for "Immens MCP Fortress" in the WordPress plugin directory, or [install from WordPress.org](https://wordpress.org/plugins/immens-mcp-fortress/).

## Pro Version

[Immens MCP Fortress Pro](https://studioimmens.com/immens-mcp-fortress-pro) unlocks:
- Unlimited access points
- 153 additional integration tools (274 total)
- Gutenberg block-level editing
- Change history with before/after snapshots
- SSE transport
- Extended audit log retention

## License

GPL-2.0-or-later — see [LICENSE](LICENSE)

## Acknowledgements

Built by [Studio Immens](https://studioimmens.com) — WordPress & AI integration specialists.

Model Context Protocol (MCP) is an open standard originally created by Anthropic, now part of the [Open Agents Alliance](https://modelcontextprotocol.io).
