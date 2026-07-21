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

### Pro Integrations (Immens CRM — 70+ tools)
- **Contacts**: create, list (with filters + pagination), get, update, delete, batch upsert
- **Deals**: create, list (with filters + pagination), get, update, delete, revenue forecast
- **Tasks**: create, list (with filters + pagination), get, update, delete
- **Companies**: create, list, get, update, delete
- **Pipeline**: list pipelines, get stages, move contacts through stages
- **Tags**: create, list, delete, assign to contact, remove from contact
- **Activity tracking**: notes (add/list), activities, transactions, recordings, AI analysis
- **Email**: create/list/get/delete/toggle templates, preview with placeholders, send branded or raw emails, log
- **Automation**: create, list, get, toggle, delete, execution logs
- **Campaigns**: create, list, get, update, delete, send
- **Forms**: create, list, get, update, delete (web-to-lead)
- **Meetings**: create, list, get, update, delete
- **Business**: products (ATV/CAC/LTV), revenue goals, management data, settings
- **Sales**: scripts, objections, proposals, contracts, recordings
- **Scoring**: list rules, calculate lead score
- **Webhooks**: create, list, update, delete
- **Custom fields**: list definitions
- **Reports**: list, get report data
- **Live Chat**: create, list, get, add messages, get messages (polling)
- **AI analysis**: contact insights, CRM statistics, dashboard alerts

## CRM Workflows

### Lead Management
1. **Create a contact** with `crm_create_contact` (email, name, phone, status, pipeline, tags, company)
2. **Batch import** with `crm_batch_contacts` (upsert multiple contacts at once)
3. **Segment** by creating and assigning tags with `crm_create_tag` + `crm_assign_tag`
4. **Move through pipeline** with `crm_move_contact_stage` (advance or move back)
5. **Track interactions** with `crm_add_contact_note` (use importance 0-2)
6. **Review progress** with `crm_get_contact` (full details + tags + notes + activities + deals + tasks)
7. **Update info** with `crm_update_contact` or change status

### Deal & Opportunity Management
1. **Create a deal** with `crm_create_deal` (title, amount, probability, pipeline, contact)
2. **Track progress** with `crm_list_deals` (filter by status, pipeline, contact)
3. **Update stages** with `crm_update_deal` (amount, probability, close date)
4. **Revenue forecast** with `crm_get_forecast` (pipeline projections + win rate)

### Task Management
1. **Create a task** with `crm_create_task` (title, priority, due date, assignee)
2. **Track work** with `crm_list_tasks` (filter by status, priority, assigned user)
3. **Update progress** with `crm_update_task` (status, priority)

### Company Management
1. **Create a company** with `crm_create_company` (name, VAT, sector, industry, size)
2. **Browse companies** with `crm_list_companies` (search by name or domain)
3. **Associate contacts** via `crm_update_contact` (company_id field)

### Email Communication
1. **Create or update templates** with `crm_create_email_template` (HTML body + plain text)
2. **Browse existing templates** with `crm_list_email_templates` (use active_only=true for enabled)
3. **Preview with real data** using `crm_parse_email_template` — resolves all placeholders
4. **Send branded email** with `crm_send_email`
5. **Send raw email** with `crm_send_raw_email` (custom headers, CC, BCC)
6. **Manage templates** with `crm_toggle_email_template` and `crm_delete_email_template`
7. **View email logs** with `crm_list_email_log` and `crm_get_email_log`

### Email Marketing (Campaigns)
1. **Create a campaign** with `crm_create_campaign` (name, subject, body, segment)
2. **Send immediately** with `crm_send_campaign`
3. **Track status** with `crm_list_campaigns` (filter by status)

### Automation
1. **Browse existing automations** with `crm_list_automations`
2. **Create or update** with `crm_save_automation` (events: contact_created, stage_changed, tag_added, etc.)
3. **Toggle active state** with `crm_toggle_automation`
4. **Get details** with `crm_get_automation` (conditions, actions, status)
5. **Review logs** with `crm_list_automation_log` and `crm_get_automation_log`

### Web-to-Lead (Forms)
1. **Create a form** with `crm_create_form` (name, fields, settings)
2. **Manage forms** with `crm_list_forms`, `crm_update_form`, `crm_delete_form`

### Meetings
1. **Schedule a meeting** with `crm_create_meeting` (title, date, contact, duration)
2. **Browse meetings** with `crm_list_meetings` (filter by status)
3. **Manage** with `crm_update_meeting`, `crm_delete_meeting`

### Live Chat
1. **Start a chat** with `crm_create_chat` (visitor_id)
2. **Send messages** with `crm_add_chat_message` (supports visitor, agent, ai senders)
3. **Read conversation** with `crm_get_chat` (full history) or `crm_get_chat_messages` (polling with since_id)
4. **Monitor chats** with `crm_list_chats` (filter by status or contact)

### Business Analysis
1. **Dashboard overview** with `crm_get_stats` (range: 7days, 30days, 90days, 1year, all)
2. **AI-powered analysis** with `crm_get_ai_insight` (natural language summary of trends)
3. **Business data** with `crm_get_management_data` (products with ATV/CAC/LTV, revenue goals, settings)
4. **Dashboard alerts** with `crm_get_dashboard_alerts`
5. **Manage products** with `crm_save_product` and `crm_delete_product`
6. **Set revenue targets** with `crm_save_revenue_goal` (monthly goals)
7. **Update settings** with `crm_save_settings` (default pipeline, status, tags)

### Scoring
1. **View scoring rules** with `crm_list_scoring_rules`
2. **Recalculate score** for a contact with `crm_calculate_score`

### Reports & Custom Fields
1. **Define custom fields** with `crm_list_custom_fields`
2. **Browse reports** with `crm_list_reports`
3. **Get report data** with `crm_get_report_data`

### Sales Enablement
1. **Browse sales scripts** with `crm_list_scripts` (filter by stage, category, active status)
2. **Get script details** with `crm_get_script`
3. **Review objections** with `crm_list_objections` (filter by script_id or type)
4. **View proposals** with `crm_list_proposals` (filter by status, contact)
5. **View contracts** with `crm_list_contracts` (filter by status, contact)
6. **Browse call recordings** with `crm_list_recordings` (filter by contact)

### Webhooks & Integrations
1. **Create a webhook** with `crm_create_webhook` (name, URL, events)
2. **Manage** with `crm_list_webhooks`, `crm_update_webhook`, `crm_delete_webhook`

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
