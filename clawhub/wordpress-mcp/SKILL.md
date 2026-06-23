---
name: wordpress-mcp
description: Connect OpenClaw to any WordPress site running Immens MCP Fortress. Manage posts, pages, media, comments, users, WooCommerce, SEO, translations, and 274+ WordPress operations via MCP tools. Secure bearer-token authentication with SHA-256 hashed API keys, IP whitelisting, and rate limiting.
license: GPL-2.0-or-later
compatibility: openclaw
tags:
  - wordpress
  - mcp
  - cms
  - woocommerce
  - seo
  - content-management
  - automation
  - web-development
  - website-management
  - publishing
  - blogging
  - admin
  - writing
  - ecommerce
metadata:
  publisher: Studio Immens
  category: wordpress
  repository: https://github.com/Studio-Immens/immens-mcp-fortress
---
# WordPress MCP

Connect OpenClaw to any WordPress site running the Immens MCP Fortress plugin. This gives your AI agent access to 274+ WordPress management tools.

## Prerequisites

1. A WordPress site with [Immens MCP Fortress](https://wordpress.org/plugins/immens-mcp-fortress/) installed and activated
2. At least one Access Point configured with an API key (go to **MCP Fortress → Access Points** in WordPress admin)

## Configuration

Add the MCP server to your OpenClaw config:

```bash
openclaw mcp add immens-mcp-fortress \
  --url "https://YOUR_SITE.com/wp-json/immens-mcp-fortress/v1/mcp" \
  --header "Authorization: Bearer YOUR_API_KEY"
```

Replace `YOUR_SITE.com` with your WordPress site URL and `YOUR_API_KEY` with the key from your Access Point.

## Auto-Discovery

If your WordPress site has Immens MCP Fortress v1.1.1+, OpenClaw can auto-discover the server via:

```
https://YOUR_SITE.com/.well-known/openclaw
```

## Available Tools

Once connected, your AI agent gains access to 121 free tools (274 with Pro):

### Content Management
- Posts & Pages: full CRUD, search, count, find/replace
- Media: upload, list, update, delete
- Comments: moderate, approve, spam, trash

### Users & Taxonomy
- Users: list, get, create, update, delete
- Categories, Tags, Terms: full CRUD with meta
- Menus: create, update, delete menu items

### Block Editor
- Reusable blocks: CRUD
- Templates: list, get, update
- Global styles: get, update

### Plugin Integrations (Free)
- WooCommerce: products, orders, customers, store stats
- Yoast SEO / RankMath: SEO analysis, sitemap, social settings
- Polylang: languages, translations
- Loco Translate: projects, translations
- Contact Form 7: forms, submissions, stats
- Code Snippets: CRUD, toggle
- W3 Total Cache: flush caches

### Pro Integrations (Immens CRM)
- Full contact lifecycle: create, list, get, update, delete
- Pipeline management: view pipelines, move contacts through stages
- Tagging system: create tags, assign/remove tags from contacts
- Activity tracking: notes, activities, transactions, recordings
- Email system: create templates, preview with placeholders, send branded or raw emails
- Automation engine: create, list, get, toggle, delete automations
- Business management: products with financial metrics, revenue goals
- Sales tools: scripts with objections, proposals, contracts
- AI analysis: contact insights, CRM statistics, dashboard alerts

## CRM Workflows

### Lead Management
1. **Create a contact** with `crm_create_contact` (upserts on duplicate email)
2. **Segment** by creating and assigning tags with `crm_create_tag` + `crm_assign_tag`
3. **Move through pipeline** with `crm_move_contact_stage` (advance or move back)
4. **Track interactions** with `crm_add_contact_note` (use importance 0-10)
5. **Review progress** with `crm_get_contact` (full details + tags + notes + recent activities)
6. **Update info** with `crm_update_contact` or change status

### Email Communication
1. **Create or update templates** with `crm_create_email_template` (HTML body + plain text)
2. **Browse existing templates** with `crm_list_email_templates` (use active_only=true for enabled)
3. **Preview with real data** using `crm_parse_email_template` — resolves all placeholders:
   - Contact: `{{first_name}}`, `{{last_name}}`, `{{email}}`, `{{phone}}`, `{{id}}`
   - Pipeline: `{{pipeline}}`, `{{stage}}`
   - Financial: `{{orders_count}}`, `{{last_order_total}}`, `{{last_order_date}}`, `{{ltv}}`
   - Documents: `{{proposal_title}}`, `{{proposal_link}}`, `{{proposal_url}}`, `{{proposal_date}}`
   - Contracts: `{{contract_title}}`, `{{contract_link}}`, `{{contract_url}}`, `{{contract_date}}`
4. **Send branded email** with `crm_send_email` (logs activity to contact timeline)
5. **Send plain email** with `crm_send_raw_email` (no branding, no logging, supports custom headers)
6. **Manage templates** with `crm_toggle_email_template` and `crm_delete_email_template`

### Automation
1. **Browse existing automations** with `crm_list_automations`
2. **Create or update** with `crm_save_automation` (events: contact_created, stage_changed, tag_added, etc.)
3. **Toggle active state** with `crm_toggle_automation` (temporarily disable without losing config)
4. **Get details** with `crm_get_automation` (conditions, actions, status)
5. **Permanently delete** with `crm_delete_automation`

### Business Analysis
1. **Dashboard overview** with `crm_get_stats` (contacts, revenue, pipelines, conversions)
2. **AI-powered analysis** with `crm_get_ai_insight` (natural language summary of trends)
3. **Business data** with `crm_get_management_data` (products with ATV/CAC/LTV, revenue goals)
4. **Dashboard alerts** with `crm_get_dashboard_alerts` (unread_only=true for new notifications)
5. **Manage products** with `crm_save_product` and `crm_delete_product`
6. **Set revenue targets** with `crm_save_revenue_goal` (monthly goals)

### Sales Enablement
1. **Browse sales scripts** with `crm_list_scripts` (filter by stage, category, active status)
2. **Get script details** with `crm_get_script` (full content with scores and timing)
3. **Review objections** with `crm_list_objections` (filter by script_id)
4. **View proposals** with `crm_list_proposals` (commercial offers sent to contacts)
5. **View contracts** with `crm_list_contracts` (agreements, post-approval stage)

## Security

- API keys stored as SHA-256 hashes (never plain text)
- IP whitelisting per access point (IPv4/IPv6 CIDR)
- Rate limiting per access point
- Full audit log of all MCP operations
- OAuth 2.1 with PKCE S256 available for one-click connections

## Troubleshooting

If tools are not showing up:
1. Verify the Access Point is **enabled** in WordPress admin
2. Check the IP whitelist includes OpenClaw's IP
3. Verify the API key is correct (format: `imf_` + 64 hex characters)
4. Check the audit log in MCP Fortress → Audit Log for request details

## Pro Version

Upgrade at [studioimmens.com](https://studioimmens.com/immens-mcp-fortress-pro) for unlimited access points and 153 additional integration tools.
