=== Immens MCP Fortress â€” AI-Powered WordPress Manager for Claude, ChatGPT, Gemini & Cursor ===
Contributors: innovazioneweb
Tags: mcp, ai, gutenberg, rest-api, security
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.3.10
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let AI manage your WordPress site. Connect Claude, ChatGPT, Gemini or Cursor to create content, manage media, and control settings â€” securely, no developer needed.

== Description ==

**Your AI agents should manage your WordPress site. Most plugins can't deliver that.**

They break. They expose your data. They can't create pages, manage media, or control settings through real conversation.

Immens MCP Fortress is the first production-grade MCP (Model Context Protocol) server built for WordPress.

### What makes it different

* **50+ MCP tools** â€” Posts, pages, media, comments, taxonomy, users, menus, blocks, templates, global styles, and more. Your AI can do everything a human admin can do.
* **Multi-access-point architecture** â€” Create multiple independent access points, each with its own API key. Enable or disable anytime.
* **OAuth 2.0/2.1 with PKCE** â€” One-click connect with Dynamic Client Registration. No manual API key copying required.
* **Streamable HTTP transport** â€” Modern MCP protocol (2025-11-25) for efficient, real-time communication.

### Security that lets you sleep at night

* **SHA-256 hashed API keys** â€” Keys are never stored in plain text. Even if the database is compromised, your access points are safe.
* **IP whitelisting** â€” Restrict access to specific IP addresses or CIDR ranges per access point.
* **Rate limiting** â€” Protect your site from abuse with configurable rate limits per access point.
* **Full audit log** â€” Every AI-originated action is logged with timestamps and details.

### Compatible with every major AI client

Claude Desktop, Claude Code, ChatGPT, Cursor, Windsurf, Cline, Gemini, and any MCP-compatible client.

### Free tier includes

* **2 access points** â€” Enough to connect Claude and ChatGPT simultaneously.
* **50+ core WordPress tools** â€” Full read/write access to posts, pages, media, comments, and more.
* **OAuth 2.0/2.1** â€” One-click connection with PKCE.

---

**Need unlimited access points, Gutenberg block-level editing, 130+ integration tools, and change history?** [Upgrade to Immens MCP Fortress Pro â†’](https://studioimmens.com)

== Installation ==

1. Upload the `immens-mcp-fortress` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to MCP Fortress â†’ Access Points in the admin menu
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

= 1.3.10 =
* SECURITY: read tools now enforce per-object authorization (read_post). Without it, a user with edit_posts could read drafts and private posts owned by others: wp_parse_blocks, yoast_get_post_seo, rankmath_get_post_seo, polylang_get_post_translations.
* SECURITY: write tools for Rank Math, Yoast and Polylang now enforce edit_post per object, closing the same cross-owner write path already fixed in the other integrations.

= 1.3.9 =
* SECURITY: fix privilege escalation through the REST API gateway tool (wp_rest_api_request). It required only the read capability and could be used to create an administrator through /wp/v2/users. It now requires manage_options.
* SECURITY: the REST gateway blocks privileged routes (users, application passwords, plugin/theme install, settings, marketplace, MCP/OAuth endpoints) regardless of access point permissions.
* SECURITY: REST namespace permissions are now deny-by-default.
* SECURITY: removed the legacy /mcp/{api_key} route, which leaked bearer credentials into web server, CDN and browser-history logs.
* Fixed params being ignored for DELETE requests in the REST gateway tool.

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
