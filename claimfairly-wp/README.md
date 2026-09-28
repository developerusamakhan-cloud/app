# ClaimFairly for WordPress

There are two parts, and you install both:

| Part | Folder | Zip to upload | What it does |
|---|---|---|---|
| **Theme** | `claimfairly/` | `dist/claimfairly-theme.zip` | Design, page templates, trust blocks, structured data, and the one-click importer that creates every page |
| **Plugin** | `claimfairly-tools/` | `dist/claimfairly-tools-plugin.zip` | The 5 calculators plus the state rules data. New tools get added here over time |

The tools live in a plugin so they keep working even if you change the theme later.

## Install (about 10 minutes)

1. **Plugin first.** Go to *Plugins > Add New > Upload Plugin*, choose `claimfairly-tools-plugin.zip`, then click *Install* and *Activate*.
2. **Theme.** Go to *Appearance > Themes > Add New > Upload Theme*, choose `claimfairly-theme.zip`, then click *Install* and *Activate*.
3. **Import everything.** Go to *Appearance > ClaimFairly Setup* and click **Import everything**. This creates:
   - Home, About, Editorial Policy, Methodology, Disclaimer, Privacy Policy and Contact
   - The 5 tool pages
   - 12 guides, sorted into 5 categories
   - 5 "How much of a $X settlement will I get?" pages
   - 4 injury pages and 3 insurer pages
   - The States hub and **40 state pages as drafts**
   - All 4 menus, the homepage, the Guides page, "Post name" permalinks and the site name

It is safe to run setup again. Pages you have edited are never overwritten.

## After importing (do these before you promote the site)

1. **Users > Profile:** set your real **Display name** and a short **Biographical Info**. The author box, bylines, About page and homepage note all use them.
2. **About page:** open it while logged in. A yellow box, visible only to you, marks where to add a few lines about yourself. Write them, then delete the box.
3. **Appearance > Customize > ClaimFairly settings:** upload your photo for the homepage founder note, and add your profile links (LinkedIn, X and so on).
4. **State pages (Pages > States):** each one is a draft. Check the facts box against the state's official statute and insurance department. Add the source links in `wp-content/plugins/claimfairly-tools/data/states.json` and set `"verified": true`, then publish the page. Start with the first 10: CA, TX, FL, NY, GA, PA, IL, OH, NC, MI.
5. **SEO plugin:** install **Rank Math** or **Yoast** (one only). The importer already filled in each page's SEO title, meta description and focus keyword for both plugins.
6. In Google Search Console, submit the sitemap your SEO plugin creates.

## Design

- A white, clean base with soft tinted sections (mint, lavender, sky), one heavy geometric font (Plus Jakarta Sans, self-hosted, 27 KB), and a highlighted word in the hero.
- Each calculator has its own color: blue for the estimator, violet for diminished value, rose for pain and suffering, orange for take-home and navy for the demand letter.
- The hero "image" is a live-looking calculator preview built in HTML, so it is sharp and fast.
- There are no fake stats and no fake testimonials. Every number on the homepage is true.
- There are no long dashes in any file or page.

## Calculators (plugin)

| Tool | Shortcode | Presets |
|---|---|---|
| Car accident settlement estimator | `[cf_tool name="settlement-estimator"]` | `state="CA"` `severity="moderate"` |
| Diminished value (17c) | `[cf_tool name="diminished-value"]` | |
| Pain and suffering | `[cf_tool name="pain-and-suffering"]` | `severity="serious"` |
| Settlement take-home | `[cf_tool name="settlement-take-home"]` | `amount="50000"` |
| Demand letter (PDF) | `[cf_tool name="demand-letter"]` | `type="injury\|property\|dv"` |
| State facts box | `[cf_state_facts state="CA"]` | |
| All states table | `[cf_state_table]` | |

The tools pass numbers to each other. For example, the estimator's "See what I'd take home" button opens the take-home calculator with the amount already filled in. Everything runs in the visitor's browser, nothing is stored, and each tool's script loads only on pages that use it.

**Adding a tool later:** create `views/{name}.php` and `assets/js/{name}.js` in the plugin, then add it to `cft_tools()` in `includes/tools.php`.

## Content shortcodes (theme)

```
[cf_faq]
[cf_q q="Question?"]Answer.[/cf_q]
[/cf_faq]                          FAQ accordion + FAQPage schema
[cf_note type="info|tip|warning"]Text[/cf_note]
[cf_editor_note]Only logged-in editors see this[/cf_editor_note]
[cf_disclaimer] [cf_sources] [cf_author_box] [cf_related] [cf_last_reviewed]
```

## Editing the starter content

The page text lives in `claimfairly/content/` as Markdown files, with the SEO title, excerpt, sources and related links at the top of each file. After the import, edit pages normally in WordPress. The Markdown files are only the starting point.
