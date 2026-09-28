# ClaimFairly for WordPress

There are two parts, and you install both:

| Part | Folder | Zip to upload | What it does |
|---|---|---|---|
| **Theme** | `claimfairly/` | `dist/claimfairly-theme.zip` | Design, page templates, trust blocks, structured data, and the one-click importer that creates every page |
| **Plugin** | `claimfairly-tools/` | `dist/claimfairly-tools-plugin.zip` | The 5 calculators plus the state rules data. New tools get added here over time |
| **Brand files** | `claimfairly/assets/brand/` | `dist/claimfairly-brand-assets.zip` | Logo, favicon and share image files for social profiles, directories and anywhere else you need them |

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
   - The "By injury" and "By insurer" hub pages
   - A **share image** (1200x630) for every page, set as its featured image
   - The **site icon** (favicon)
   - All 4 menus, the homepage, the Guides page, "Post name" permalinks and the site name

It is safe to run setup again. Pages you have edited are never overwritten.

## After importing (do these before you promote the site)

1. **Users > Profile:** set your real **Display name** and a short **Biographical Info**. The author box, bylines, About page and homepage note all use them.
2. **About page:** open it while logged in. A yellow box, visible only to you, marks where to add a few lines about yourself. Write them, then delete the box.
3. **Appearance > Customize > ClaimFairly settings:** upload your photo for the homepage founder note, and add your profile links (LinkedIn, X and so on).
4. **State pages (Pages > States):** each one is a draft. Check the facts box against the state's official statute and insurance department. Add the source links in `wp-content/plugins/claimfairly-tools/data/states.json` and set `"verified": true`, then publish the page. Start with the first 10: CA, TX, FL, NY, GA, PA, IL, OH, NC, MI.
5. **SEO plugin:** install **Rank Math** or **Yoast** (one only). The importer already filled in each page's SEO title, meta description and focus keyword for both plugins.
6. In Google Search Console, submit the sitemap your SEO plugin creates.

## Logo, favicon and share images

| File | Use it for |
|---|---|
| `logo.svg`, `logo.png` | Main logo on light backgrounds (the PNG is 1276x220, transparent) |
| `logo-white.svg`, `logo-white.png` | Logo on dark backgrounds (amber icon, white text) |
| `logo-mark.svg`, `logo-mark-512.png`, `logo-mark-white.svg` | Square icon alone: social profile pictures, app icons |
| `favicon.ico`, `favicon.svg`, `favicon-16x16.png`, `favicon-32x32.png` | Browser tab icon |
| `apple-touch-icon.png` (180), `android-chrome-192x192.png`, `android-chrome-512x512.png`, `site.webmanifest` | Phone home screen icons |
| `og-default.jpg` | Default share image (1200x630) |

Every page also has its own share image in `assets/og/`, showing its title and its own color. The importer uploads each one to the Media Library and sets it as the page's featured image, so Facebook, X, LinkedIn, WhatsApp and SEO plugins all pick it up automatically. To regenerate the images after changing titles, run `node build/og-images.mjs` (requires Node and Playwright).

## Blog

The articles live in a proper blog at `/blog/` (sites set up earlier are moved over automatically, and `/guides/` redirects there):

- **Blog page:** the newest article as a large featured card, category chips with post counts, a search box that only searches articles, and a grid of cards.
- **Articles:** a reading progress bar, a meta card (author, reviewed date, reading time, number of sources), share buttons (X, Facebook, LinkedIn, WhatsApp, email and copy link; plain links, no tracking scripts), related articles from the same category, and previous / next links.
- **Categories:** each has its own page and description.
- **New articles:** write a normal WordPress post, pick a category, add a featured image if you like, and fill in the Trust & SEO box. Everything else is automatic.

## Author

The site owner is shown as **James** everywhere: bylines, author boxes, the homepage note, the About and Editorial pages, and the schema. Change the name, bio and photo in *Appearance > Customize > ClaimFairly settings*. It does not depend on your WordPress account's display name.

## Sitemap

- **HTML sitemap** at `/sitemap/`: every published page, grouped for people.
- **XML sitemap** for search engines: WordPress's built-in `/wp-sitemap.xml`, or `/sitemap_index.xml` when Rank Math or Yoast is active.
- Both are linked in the footer.

## SEO built in

- **Schema on every page**, as one connected graph: Organization (with logo), WebSite, Person (you, as founder), WebPage (AboutPage, ContactPage and CollectionPage where they fit), BreadcrumbList, Article on guides and state, injury and insurer pages, WebApplication on tools, FAQPage on every page with an FAQ, and ItemList on the homepage and guides page. State pages also say which state they are about.
- **Open Graph and Twitter tags**, a meta description and the SEO title on every page.
- **When Rank Math or Yoast is active,** the theme leaves titles, descriptions, social tags and the base graph to them. It keeps adding WebApplication and FAQPage, which those plugins do not generate.
- **Internal links:** every content page has 4 to 12 links inside its text and at least 3 links from other pages. Hubs link down to every child page, settlement pages link to the next and previous amounts, and each state page lists other published states with the same fault rule. Links to draft state pages never appear, so there are no broken links while you verify states.
- **Content depth:** content pages run 1,150 to 1,700 words as rendered, each with 6 to 8 FAQs. Privacy, Disclaimer and Contact are intentionally shorter.
- **Audit:** run `python3 build/audit.py` to see word counts, FAQs and links in and out for every page.

## Design

- **Navy and amber brand:** navy (#14213D) for buttons, links and the logo, a warm amber (#FFC53D) highlight on the hero headline and on link underlines, and soft cream sections. One heavy geometric font (Plus Jakarta Sans, self-hosted, 27 KB).
- To change the brand color later, edit the `--cf-brand` and `--cf-accent` variables at the top of `assets/css/main.css`, then regenerate the logo and share images with the scripts in `build/` and change `CLAIMFAIRLY_BRAND_VERSION` in `functions.php` so setup swaps in the new images.
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
