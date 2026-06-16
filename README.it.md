# Immens MCP Fortress

**Server MCP di livello militare per WordPress** — Trasforma qualsiasi sito WordPress in un server MCP sicuro con architettura multi-access-point. Connetti Claude, ChatGPT, Cursor, OpenCode, OpenClaw, Windsurf, Cline e qualsiasi agente AI compatibile MCP per gestire il tuo sito.

[![Versione](https://img.shields.io/badge/versione-1.1.0-blue.svg)](https://studioimmens.com)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-brightgreen.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![Licenza](https://img.shields.io/badge/licenza-GPL--2.0-or-later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![MCP](https://img.shields.io/badge/MCP-2025--11--25-orange.svg)](https://modelcontextprotocol.io)

---

## Cosa Fa

Immens MCP Fortress espone l'intero sito WordPress come server MCP (Model Context Protocol). Gli agenti AI possono leggere, creare, aggiornare ed eliminare contenuti tramite conversazione naturale — con sicurezza di livello militare a ogni livello.

## 274+ Tool MCP (121 free + 153 Pro)

### Core WordPress (Free)

| Categoria | Tool | Operazioni |
|-----------|------|------------|
| Articoli | 8 | Lista, Dettaglio, Crea, Aggiorna, Elimina, Conteggio, Completo, Trova/Sostituisci |
| Pagine | 8 | Lista, Dettaglio, Crea (singola + batch), Aggiorna, Elimina, Conteggio |
| Media | 7 | Lista, Dettaglio, Carica (file + URL), Aggiorna, Elimina, Conteggio |
| Commenti | 8 | Lista, Dettaglio, Crea, Aggiorna, Elimina, Approva, Spam, Cestina |
| Utenti | 5 | Lista, Dettaglio, Crea, Aggiorna, Elimina |
| Tassonomie | 18 | Categorie, Tag, Termini (CRUD + Meta) |
| Menu | 9 | Menu e Voci menu (CRUD) |
| Blocchi | 6 | Blocchi riutilizzabili (CRUD + Tipi) |
| Template | 3 | Lista, Dettaglio, Aggiorna |
| Stili | 2 | Stili globali (Get/Update) |
| Sito | 2 | Impostazioni sito |
| Meta | 4 | Meta post + Termini |
| Revisioni | 3 | Lista, Dettaglio, Ripristina |
| Ricerca | 1 | Ricerca su tutti i contenuti |
| Plugin | 1 | Lista plugin installati |
| Temi | 1 | Lista temi installati |
| CPT | 1 | Lista custom post type |

### Integrazioni Plugin (Free)

| Plugin | Tool |
|--------|------|
| WooCommerce | 10 (Prodotti, Ordini, Clienti, Statistiche) |
| Yoast SEO | 6 (SEO Post, Impostazioni, Sitemap, Social) |
| RankMath SEO | 6 (Analytics, SEO, Impostazioni, Sitemap) |
| Loco Translate | 5 (Progetti, Traduzioni, Export) |
| Contact Form 7 | 4 (Form, Statistiche, Invii) |
| Polylang | 6 (Lingue, Traduzioni, Impostazioni) |
| Code Snippets | 6 (Snippet CRUD + Attiva/Disattiva) |
| W3 Total Cache | 3 (Impostazioni, Svuota cache) |

### Pro (153 tool aggiuntivi)

| Integrazione | Tool |
|-------------|------|
| Gutenberg Block-Level | 6 (modifica blocchi dentro i post) |
| ClassyBlocks Pro | 14 (animazioni) |
| Greenshift | 16 (impostazioni, stylebook, AI) |
| Stackable | 8 (impostazioni, colori, tipografia) |
| Primary Source | 17 (gestione contenuti AI) |
| Immens CRM | 31 (contatti, pipeline, tag, automazioni) |
| Immens Integration | 22 (dashboard, moduli, business info) |
| The SEO Framework | 12 (analisi SEO, bulk update) |
| TranslatePress | 10 (gestione traduzioni) |
| Elementor | 10 (template, pagina, colori, font) |
| ACF | 8 (field groups, campi, opzioni) |

## Avvio Rapido

### 1. Installa
Carica la cartella `immens-mcp-fortress` in `/wp-content/plugins/` e attiva il plugin.

### 2. Crea un Access Point
1. Vai a **MCP Fortress → Access Point** nel pannello admin
2. Clicca **Aggiungi Access Point**
3. Assegna un nome (es. "Claude Desktop")
4. Seleziona i permessi per categoria
5. **Copia la API key** (formato: `imf_xxxxxxxxxxxx...`)

### 3. Connetti il Tuo Client AI

#### Claude Desktop
1. Impostazioni → Connectors → Aggiungi connector personalizzato
2. Incolla l'URL dell'endpoint MCP
3. Inserisci la API key

#### OpenCode
Aggiungi al tuo `opencode.json`:
```json
{
  "mcp": {
    "immens-mcp-fortress": {
      "type": "remote",
      "url": "https://tuosito.com/wp-json/immens-mcp-fortress/v1/mcp",
      "enabled": true,
      "headers": {
        "Authorization": "Bearer TUA_API_KEY"
      }
    }
  }
}
```

#### OpenClaw
```bash
openclaw mcp add immens-mcp-fortress \
  --url "https://tuosito.com/wp-json/immens-mcp-fortress/v1/mcp" \
  --header "Authorization: Bearer TUA_API_KEY"
```
Oppure installa via ClawHub:
```bash
openclaw skills install wordpress-mcp
```

#### Cursor / Windsurf / Cline
- Transport: `streamable-http`
- URL: `https://tuosito.com/wp-json/immens-mcp-fortress/v1/mcp`
- Headers: `Authorization: Bearer TUA_API_KEY`

## Architettura

### Autenticazione
| Metodo | Descrizione |
|--------|-------------|
| Bearer token (API Key) | Per connessioni dirette da client AI |
| Parametro URL inline | `?api_key=...` per client senza supporto header |
| OAuth 2.1 PKCE | Connessioni one-click con schermata di consenso |

### Trasporti
- **HTTP Streamable** (MCP 2025-11-25) — JSON-RPC via HTTP POST
- **SSE** (Server-Sent Events) — per streaming e client legacy

### Endpoint
| Endpoint | Scopo |
|----------|-------|
| `/immens-mcp-fortress/v1/mcp` | Endpoint MCP principale |
| `/.well-known/opencode` | Auto-discovery per OpenCode |
| `/.well-known/openclaw` | Auto-discovery per OpenClaw |
| `/.well-known/oauth-authorization-server` | Metadata OAuth |

## Sicurezza

- **API key**: SHA-256 hash, mai in chiaro
- **Formato key**: `imf_` + 64 caratteri hex
- **IP whitelist**: CIDR IPv4 e IPv6 per access point
- **Rate limit**: richieste/minuto configurabile
- **Audit log**: ogni chiamata MCP registrata
- **OAuth PKCE**: S256 code challenge, registrazione dinamica (RFC 7591)

## Requisiti

- WordPress 6.0+
- PHP 7.4+
- HTTPS raccomandato per OAuth in produzione

## Versione Pro

[Immens MCP Fortress Pro](https://studioimmens.com/immens-mcp-fortress-pro) sblocca:
- Access point illimitati
- 153 tool di integrazione aggiuntivi (274 totali)
- Modifica blocchi Gutenberg a livello singolo
- Cronologia modifiche con snapshot before/after
- Trasporto SSE
- Retention estesa audit log

## Licenza

GPL-2.0-or-later — vedi [LICENSE](LICENSE)

## Riconoscimenti

Creato da [Studio Immens](https://studioimmens.com) — specialisti in integrazione WordPress & AI.

Model Context Protocol (MCP) è uno standard aperto creato da Anthropic, ora parte della [Open Agents Alliance](https://modelcontextprotocol.io).
