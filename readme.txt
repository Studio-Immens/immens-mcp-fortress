=== Immens MCP Fortress — AI-Powered WordPress Manager for Claude, ChatGPT, Gemini & Cursor ===
Contributors: innovazioneweb
Tags: mcp, ai, gutenberg, rest-api, security
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.3.5
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let AI manage your WordPress site. Connect Claude, ChatGPT, Gemini or Cursor to create content, manage media, and control settings — securely, no developer needed.

== Description ==

**Your AI agents should manage your WordPress site. Most plugins can't deliver that.**

They break. They expose your data. They can't create pages, manage media, or control settings through real conversation.

Immens MCP Fortress is the first production-grade MCP (Model Context Protocol) server built for WordPress.

### What makes it different

* **50+ MCP tools** — Posts, pages, media, comments, taxonomy, users, menus, blocks, templates, global styles, and more. Your AI can do everything a human admin can do.
* **Multi-access-point architecture** — Create multiple independent access points, each with its own API key. Enable or disable anytime.
* **OAuth 2.0/2.1 with PKCE** — One-click connect with Dynamic Client Registration. No manual API key copying required.
* **Streamable HTTP transport** — Modern MCP protocol (2025-11-25) for efficient, real-time communication.

### Security that lets you sleep at night

* **SHA-256 hashed API keys** — Keys are never stored in plain text. Even if the database is compromised, your access points are safe.
* **IP whitelisting** — Restrict access to specific IP addresses or CIDR ranges per access point.
* **Rate limiting** — Protect your site from abuse with configurable rate limits per access point.
* **Full audit log** — Every AI-originated action is logged with timestamps and details.

### Compatible with every major AI client

Claude Desktop, Claude Code, ChatGPT, Cursor, Windsurf, Cline, Gemini, and any MCP-compatible client.

### Free tier includes

* **2 access points** — Enough to connect Claude and ChatGPT simultaneously.
* **50+ core WordPress tools** — Full read/write access to posts, pages, media, comments, and more.
* **OAuth 2.0/2.1** — One-click connection with PKCE.

---

**Need unlimited access points, Gutenberg block-level editing, 130+ integration tools, and change history?** [Upgrade to Immens MCP Fortress Pro →](https://studioimmens.com)

== Installation ==

1. Upload the `immens-mcp-fortress` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to MCP Fortress → Access Points in the admin menu
4. Create your first access point and copy the API key
5. Configure your MCP client with the endpoint URL and API key

== Frequently Asked Questions ==

= What is MCP? =

MCP (Model Context Protocol) is an open standard created by Anthropic that allows AI assistants to interact with external tools and data sources. This plugin implements an MCP server inside WordPress.

= Which AI clients are compatible? =

Claude Desktop, Claude Code, ChatGPT, Cursor, Windsurf, Cline, and any MCP-compatible client.

= Is this plugin secure? =

Yes. API keys are stored as SHA-256 hashes, never in plain text. Rate limiting, IP whitelisting, and full audit logging are built in. OAuth 2.0/2.1 with PKCE is supported for one-click connections.

= How many access points can I create? =

The free tier includes 2 access points. Upgrade to Pro for unlimited.

== Changelog ==

= 1.0.0 =
* Initial release
* 50+ MCP tools for core WordPress operations
* Multi-access-point architecture with API key authentication
* Gutenberg reusable blocks CRUD
* Streamable HTTP transport (MCP 2025-11-25)
* OAuth 2.0/2.1 with PKCE and Dynamic Client Registration
* IP whitelisting with CIDR support
* Rate limiting per access point
* Full audit logging with configurable retention
* Per-access-point tool permissions (read/write per category)
* SSE Transport heartbeat for legacy clients
* Admin dashboard with quick setup guides
