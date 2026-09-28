# ClaimFairly WordPress Theme

This is a lightweight, trust-first theme for **ClaimFairly.com**. It uses no page builder, no web fonts and no jQuery, so pages stay fast (the target is PageSpeed 90+ on mobile).

- Theme source: `wordpress-theme/claimfairly/`
- Ready-to-upload zip: `wordpress-theme/dist/claimfairly.zip`

## 1. Install

1. Download `dist/claimfairly.zip`.
2. In WordPress, go to **Appearance → Themes → Add New → Upload Theme**, choose the zip, then click **Install** and **Activate**.
3. Go to **Settings → Permalinks** and select **Post name** (`/%postname%/`).

## 2. First-time setup (about 20 minutes)

| Step | Where | What to do |
|---|---|---|
| 1 | Users → Profile | Set **Display name** to your real name and fill in **Biographical Info**. The author box uses both. |
| 2 | Appearance → Customize → ClaimFairly settings | Check the homepage headline, disclaimer, contact email, About URL and your official profile URLs. |
| 3 | Appearance → Customize → Site Identity | Set the site title to "ClaimFairly". A logo is optional (the built-in shield mark is used otherwise). Add a site icon (512×512). |
| 4 | Pages → Add New | Create **About, Editorial Policy, Methodology, Disclaimer, Privacy Policy, Contact** and **Home**, and a blank **Guides** page. |
| 5 | Settings → Reading | Choose "A static page": Homepage = Home, Posts page = Guides. |
| 6 | Appearance → Menus | Build **Primary** (Tools ▸ each tool, Guides, States, About), **Footer: Tools** and **Footer: Site & policies** (the 6 policy pages). |
| 7 | Plugins | Install **Rank Math** *or* **Yoast SEO** (only one) for title tags, meta descriptions and the sitemap. The theme detects either one and stops printing duplicate schema. |

## 3. The "ClaimFairly: Trust & SEO" box

Every page and post has this box below the editor:

| Field | What it does |
|---|---|
| **Page type** | Choose from Standard, Tool, Guide, State, Injury or Insurer. **Tool** pages appear automatically on the homepage and get `WebApplication` schema. Every page type except Standard shows the byline, the reviewed date, sources, an author box and related tools. |
| **Last reviewed** | Shown under the H1 and in the Sources box. Update it every time you re-check the page. |
| **Sources** | Add one source per line in the format `Title | https://url`. |
| **Related tools** | Add one per line in the format `Label | /url/`. If you leave it empty, 3 tool pages are picked automatically. |
| **Card summary** | A single line shown on homepage and related-tool cards. |
| **Hide author box** | Hides the author box on that page. |

Blog posts default to the **Guide** type. Pages default to **Standard**.

## 4. Shortcodes

Paste these into a **Shortcode** block:

```
[cf_calculator name="diminished-value"]     ← calculators (added one by one)

[cf_faq]
[cf_q q="What is diminished value?"]Answer text here.[/cf_q]
[cf_q q="Does the 17c formula undervalue cars?"]Answer text here.[/cf_q]
[/cf_faq]                                   ← FAQ accordion + FAQPage schema

[cf_note type="tip"]Text[/cf_note]          ← types: info, tip, warning
[cf_disclaimer]  [cf_sources]  [cf_author_box]  [cf_related]  [cf_last_reviewed]
```

## 5. Structured data the theme outputs

| Page | Schema |
|---|---|
| Home | Organization, WebSite *(skipped if Rank Math/Yoast is active)* |
| Tool pages | WebApplication (free, FinanceApplication) |
| Guide/state/injury/insurer pages and posts | Article with author and dateModified = last reviewed *(skipped if an SEO plugin is active)* |
| Inner pages | BreadcrumbList *(skipped if an SEO plugin is active)* |
| Any page with `[cf_faq]` | FAQPage |

## 6. Adding a calculator (how the next steps plug in)

Each tool is two files:

- `inc/tools/{name}.php`: the form and result markup, using the shared classes (`cf-form`, `cf-field`, `cf-input-group`, `cf-choice`, `cf-result`, `cf-range`, `cf-formula`, `cf-assumptions`, `cf-changes`)
- `assets/js/tools/{name}.js`: the logic. It registers the tool with `ClaimFairly.tool('{name}', fn)` and uses the helpers in `assets/js/cf-core.js` (`money`, `parse`, `pct`, `values`, `validate`, `onSubmit`, `showResult`, `rangeHTML`, `copy`, and buttons marked `data-cf-print` or `data-cf-copy`).

The script loads only on pages that use that calculator. Everything runs in the browser, and nothing is stored or sent anywhere.

## 7. Other built-in behaviour

- Comments and XML-RPC are turned off, and the emoji scripts and head clutter are removed.
- The header is sticky, the mobile menu is accessible, and there is a skip link and visible focus styles.
- The print stylesheet prints only the result, the formula and the sources.
- The footer always shows the disclaimer plus the line "not a law firm, not affiliated with any insurer".
