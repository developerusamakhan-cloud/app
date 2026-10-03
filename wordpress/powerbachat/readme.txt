=== PowerBachat ===
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later

Theme for PowerBachat.com: electricity bill calculators, unit rates and solar prices
for Pakistan, India and Bangladesh.

== Installation ==

1. Zip the `powerbachat` folder (or use the supplied powerbachat.zip).
2. WordPress admin → Appearance → Themes → Add New → Upload Theme → Activate.
3. Settings → Reading → "Your homepage displays" can stay on "Your latest posts";
   the theme's front-page.php is used either way. To list guides on their own page,
   pick a static front page plus a "Guides" posts page.
4. Settings → Permalinks → "Post name".

== Before launch: verify the rates ==

Every tariff slab, tax rate and solar price assumption lives in `inc/data.php`.
They are reference values and must be checked against the latest official notification
(NEPRA for Pakistan; TNERC, KSERC, UPERC, MERC, KERC and DERC for India; BERC for
Bangladesh) before going live.
After checking, update "Tariffs last checked" in the Customizer so the date shown on
the site is honest.

Price boards are edited in Appearance → Customize → PowerBachat → Rates & prices:
solar panels (Pakistan, India, Bangladesh), IPS sets and solar batteries (Bangladesh).
They ship with sample rows. Until you save your own rows, admins see a reminder under
each board, and visitors never see that reminder.

== Home page ==

Sections (in order): hero + live bill calculator, rate ticker, utility directory,
slab chart, solar sizing tool, price board, guides, method, FAQ, alerts sign-up.
Reorder or remove them with the `powerbachat_home_sections` filter.

== Country by location ==

Each visitor sees only their own country (Pakistan, India or Bangladesh), picked
automatically from their location. There is no switch on the site.

How the country is chosen (first match wins):
1. The URL: pages under /pk/, /in/ and /bd/ always show that country, so Google and
   shared links see the right content whatever their location.
2. A remembered choice (the pb_cc cookie, kept for 30 days).
3. The country header your host or CDN sends (Cloudflare CF-IPCountry, CloudFront,
   server GeoIP). On Cloudflare, turn on "IP Geolocation" (Network settings);
   this is the fastest and most accurate option.
4. An online IP lookup (api.country.is), cached for a week per visitor IP. It can be
   switched off in Customize → PowerBachat → Home page.
5. Everyone outside the three countries gets the fallback country set in the
   Customizer (Pakistan by default).

Works with page caching: the page carries all three versions and the browser shows
the right one, confirming the country with /wp-json/powerbachat/v1/country on the
first visit.

Testing: add ?pb_country=in (or pk, bd) to any URL to preview another country. It is
remembered for 30 days; use ?pb_country=pk to go back.

Guides: put a post in a category with the slug pakistan, india or bangladesh to show
it to that country only. Posts without one of those categories show to everyone.

== Ready-written content (Appearance > PowerBachat content) ==

The theme ships with finished pages and guide posts for all three countries in
/content/pk/, /content/in/, /content/bd/ and /content/blog/ (50 pages, 21 posts).
Go to Appearance > PowerBachat content and click "Create missing pages and posts". It
creates the country hubs (/pk/, /in/, /bd/), every company calculator page, the unit price and
tariff pages, the solar and battery pages, and the guide posts, with parents,
templates, excerpts and SEO fields already set. Nothing you edit is overwritten
unless you press "Re-import" on that row.

Publishing schedule: all pages go live at once. Guide posts are taken in their set
order: the first ones publish immediately ("Publish now", default 15) and the rest
are scheduled one every few days ("Then one post every", default 2 days) at 09:00
site time, so new posts keep appearing on their own. Change both numbers on the same
screen before you import. Published content never links to a post that is still
scheduled, so there are no broken links while the queue runs.

Every item has:
* An SEO title (under 60 characters) and a meta description (120 to 160 characters).
* A focus keyword used naturally a few times, never stuffed.
* Structured data: breadcrumbs, WebPage or BlogPosting, FAQPage for the FAQ
  section, and WebApplication on calculator pages.
* Internal links to related pages, all pointing at pages in the same set.

With Yoast SEO, Rank Math, All in One SEO or SEOPress active, the importer also fills
that plugin's title, description and focus keyword fields, and the theme stops
printing its own title, description and page schema so nothing is doubled.

Numbers inside the articles (rate tables, bill examples, solar costs, panel prices)
come from shortcodes that read inc/data.php and the Customizer, so they always match
the calculators. Update a rate once and every page follows.

== Building the calculator pages ==

The utility list in inc/data.php already holds the URLs from the keyword plan, e.g.
/pk/lesco-bill-calculator/, /in/tneb-bill-calculator/, /bd/desco-bill-calculator/.

1. Create a parent page with slug `pk` (or `in`, `bd`).
2. Create a child page with slug `lesco-bill-calculator`.
3. Page Attributes → Template → "Calculator page".

The template detects the company from the URL, shows the calculator pre-set to it,
your page content underneath, then the slab chart. For a URL that is not in the list,
add custom fields `pb_country` (pk|in|bd) and `pb_utility` (e.g. lesco).

All listed companies have tariff data: LESCO to K-Electric for Pakistan; TNEB, KSEB,
UPPCL, MSEDCL, BESCOM and Delhi for India; DESCO, DPDC and the BERC tariff for
Bangladesh. To add another, add a tariff table in inc/data.php and set the
company's `tariff` key.

== Shortcodes ==

[powerbachat_calculator country="pk" utility="iesco" units="300"]
[powerbachat_slab_chart country="bd"]
[powerbachat_solar country="in" units="400"]

Leave `country` empty to follow the visitor's country.

Tables used inside the articles:
[powerbachat_rate_table tariff="pk-nepra" plan="protected"]
[powerbachat_bill_table tariff="in-tneb" units="200,400,600"]
[powerbachat_solar_table country="in" sizes="3,5,10"]
[powerbachat_price_table board="bd-ips"] (boards: pk-panels, in-panels, bd-panels,
bd-ips, bd-battery)
[powerbachat_subsidy_table sizes="1,2,3,5"] (PM Surya Ghar central subsidy)
[powerbachat_battery] (battery and IPS backup calculator)

== Customizer (Appearance → Customize → PowerBachat) ==

* Home page: default country, hero headline (wrap words in *asterisks* for the
  hand-drawn underline), hero intro.
* Rates & prices: tariffs-checked date, price board rows, currency, update date.
* Alerts & footer: newsletter form action URL (Mailchimp, Brevo…), WhatsApp channel
  URL, footer disclaimer.

== Credits ==

Fonts: Fraunces, Hanken Grotesk, JetBrains Mono, Noto Nastaliq Urdu (SIL Open Font
License), loaded from Google Fonts. Icons and illustrations are original inline SVG.

== Changelog ==

= 1.3.0 =
* India and Bangladesh content: country hubs, calculators for TNEB, KSEB, UPPCL, MSEDCL, BESCOM, Delhi, DESCO and DPDC, solar, subsidy, IPS and battery pages, plus guide posts; 9 new cross-country guides.
* Post scheduling in the importer: 15 posts publish now, the rest go out one every 2 days.
* New India tariffs (UPPCL, MSEDCL, BESCOM, Delhi), India and Bangladesh price boards, PM Surya Ghar subsidy table.
* FAQ schema now finds the right section when a page has more than one "questions" heading.
* Urdu mark shown on Pakistan only; neutral search placeholder; home guides fallback links fixed.

= 1.2.0 =
* Pakistan content: 22 pages and 5 guide posts (1,100 to 1,500 words each) with SEO titles, meta descriptions, focus keywords, schema and internal links; content importer; battery backup calculator; data table shortcodes; page and post schema; long dashes removed everywhere.

= 1.1.1 =
* Shorter hero calculator: total shown first, slab breakdown folded behind a toggle (open by default on calculator pages), heading row removed, one-line protected toggle.

= 1.1.0 =
* Country picked automatically from visitor location (Cloudflare header or IP lookup); only that country's companies, rates, prices, guides and FAQs are shown; country switch removed; /pk/, /in/ and /bd/ pages always show their own country.

= 1.0.1 =
* Smaller hero heading (two lines instead of three) and tighter hero spacing.
* More compact bill estimator and bill slip, so more of the result fits on screen.

= 1.0.0 =
* First release: home page, bill calculator, slab chart, solar sizing tool, price board,
  calculator page template, shortcodes and Customizer settings.
