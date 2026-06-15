=== Immens MCP Fortress ===
Contributors: innovazioneweb
Tags: mcp, ai, gutenberg, rest-api, security
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.0.4
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

MCP server for WordPress. Connect Claude, ChatGPT, Cursor to manage your site with multi-access-point architecture and top security.

== Description ==

Immens MCP Fortress turns your WordPress site into a secure MCP (Model Context Protocol) server. AI agents like Claude, ChatGPT, and Cursor can connect directly to read, create, and manage your content through natural conversation.

= Key Features =

* **Multi-Access-Point Architecture** — Create multiple independent access points, each with its own API key, enabling/disabling anytime.
* **50+ MCP Tools** — Posts, pages, media, comments, taxonomy, users, menus, blocks, templates, global styles, and more.
* **Gutenberg Block Support** — Create, update, delete reusable blocks via MCP.
* **Streamable HTTP Transport** — Modern MCP protocol (2025-11-25) for efficient communication.
* **OAuth 2.0/2.1** — One-click connect with PKCE S256 and Dynamic Client Registration.
* **Security First** — SHA-256 hashed API keys, IP whitelisting, rate limiting, full audit log.
* **Two Access Points** — Free tier includes 2 configurable access points.

= Pro Features =

Upgrade to Immens MCP Fortress Pro on studioimmens.com for:

* **Unlimited Access Points**
* **Gutenberg Block-Level Editing** — Parse, add, remove, reorder blocks inside posts.
* **131 Integration Tools** — Primary Source, Immens CRM, ClassyBlocks Pro, SEO Framework, Greenshift, Stackable, TranslatePress, and more.
* **SSE Transport** — Support for legacy MCP clients.
* **Change History** — Before/after snapshots of every AI-originated write.
* **Per-Access-Point IP Whitelisting**
* **Extended Audit Log Retention**

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
