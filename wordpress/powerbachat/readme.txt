=== PowerBachat ===
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.6.0
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
/content/pk/, /content/in/, /content/bd/, /content/blog/ and /content/site/
(57 pages, 36 posts).
Go to Appearance > PowerBachat content and click "Create missing pages and posts". It
creates the country hubs (/pk/, /in/, /bd/), every company calculator page, the unit price and
tariff pages, the solar and battery pages, and the guide posts, with parents,
templates, excerpts and SEO fields already set. Nothing you edit is overwritten
unless you press "Re-import" on that row.

Publishing schedule: all pages go live at once. Guide posts are taken in their set
order: the first ones publish immediately ("Publish now", default 15) and the rest
are scheduled one every few days ("Then one post every", default 2 days) at 09:00
site time, so new posts keep appearing on their own. When a theme update adds more
posts, "Create missing" queues them after the last post that is already scheduled,
so the rhythm continues without two posts on the same day. The order rotates
Pakistan, India and Bangladesh, so every country gets new guides regularly. Change both numbers on the same
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

== Menu, footer and legal pages ==

The header menu and the footer are built automatically for the visitor's country:
Bill calculators, Unit rates, Solar (with dropdowns listing every page) and Guides
(the country's guide archive, e.g. /category/pakistan/). Links only appear once their
page exists, so nothing points to a 404. Assigning your own menu to "Primary menu"
in Appearance > Menus replaces the automatic one (note: a WordPress menu is the same
for every country). Add extra pages or sections with the `powerbachat_site_map` and
`powerbachat_company_pages` filters.

The footer lists every page for the country plus the company and legal pages from
/content/site/: About us, How we check rates (editorial policy),
Contact us, Privacy policy, Terms of use, Disclaimer and Cookie policy. Legal pages
are kept at their natural length rather than padded. They describe how the theme
actually works (country lookup, the pb_cc and pb_alert cookies, Google Fonts, alert
emails, contact form retention); review them with your own adviser and update them
if you add analytics, ads or other services.

== Tariff alerts (built-in newsletter) ==

The sign-up box (home page and the bottom of every page) stores subscribers in
WordPress: Tariff alerts > Subscribers, with country, status and date, plus a CSV
export.

* Double opt-in: a confirmation email is sent first (Customizer setting).
* Every email has a one-click unsubscribe link and List-Unsubscribe headers.
* New guides: when a guide post goes live (including scheduled posts), subscribers
  in that country get one short email. Posts created by the importer do not trigger
  emails until they go live on their scheduled date.
* Tariff alerts > Send an alert: write your own message (e.g. a new NEPRA
  notification) to all countries or one, with a "send me a test" button.
* Emails go out in batches of 40 every two minutes via WP-Cron.
* Unconfirmed and unsubscribed addresses are deleted after 30 days.

Install an SMTP plugin (e.g. WP Mail SMTP with Brevo, Amazon SES or your host's mail
server) so emails reach inboxes. To use Mailchimp or Brevo forms instead, paste the
form action URL in the Customizer; the built-in list is then not used for sign-ups.

== Contact form ==

[powerbachat_contact] (used on /contact-us/) saves each message under Messages in
the admin and emails it to the contact address (Customize > PowerBachat > Alerts &
footer > Contact email, default hello@powerbachat.com: make sure that mailbox
exists). Messages older than 12 months are deleted automatically. Spam protection:
hidden honeypot field and a per-visitor rate limit.

== Featured images ==

Every guide post ships with a branded cover image (content/images/{slug}.jpg,
1200 x 675) showing a short topic label rather than the full title, so it stays
readable on small cards. The importer adds it to the Media Library and sets it as
the featured image, with the post title as alt text. "Create missing" also gives
posts that already exist their cover if they have none, and swaps older theme
covers for the current design. A featured image you chose yourself is never
replaced.

Each post ends with "Related guides": up to three published guides for the same
country, so new posts get internal links as soon as they go live. Covers are
used on the guide cards, at the top of each post, in Open Graph / Twitter cards and
in the article schema. Replace any of them by setting a different featured image.
To make covers for new posts, run wordpress/tools/covers.js (see the notes at the
top of that file).

== Comments ==

Comments are switched off everywhere: no comment forms or lists, no pingbacks or
trackbacks, no comment feeds or REST endpoints, and the Comments and Discussion
screens are removed from the admin. Existing comments are hidden, not deleted. To
turn comments back on, use add_filter( 'powerbachat_disable_comments',
'__return_false' ); in a small plugin.

== Favicon ==

The theme ships its own favicon (SVG, ICO, PNG, Apple touch icon and web manifest)
in assets/img/. Uploading a Site Icon in Customize > Site Identity replaces it.

== SEO details ==

Each content file carries a primary (focus) keyword and up to four secondary
keywords. The importer writes them to Rank Math (multiple focus keywords), Yoast
(related keyphrases, Premium) and SEOPress. Without an SEO plugin, the theme prints
the title, meta description, Open Graph tags and JSON-LD: Organization (logo,
contact point), WebSite with search, BreadcrumbList, WebPage / AboutPage /
ContactPage / BlogPosting (with keywords, word count, section and country),
FAQPage, WebApplication for calculators, and CollectionPage for the country guide
archives. The title separator is a plain bar, not a dash.

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
[powerbachat_contact] (contact form)
[powerbachat_contact_email], [powerbachat_updated], [powerbachat_legal_place]
(used by the legal pages)

== Customizer (Appearance → Customize → PowerBachat) ==

* Home page: default country, hero headline (wrap words in *asterisks* for the
  hand-drawn underline), hero intro.
* Rates & prices: tariffs-checked date, price board rows, currency, update date.
* Alerts & footer: newsletter form action URL (optional, Mailchimp or Brevo),
  double opt-in, new guide emails, contact email, governing law country, footer
  credit ("Made with love by", name and link), WhatsApp channel URL, footer
  disclaimer.

== Credits ==

Fonts: Fraunces, Hanken Grotesk, JetBrains Mono, Noto Nastaliq Urdu (SIL Open Font
License), loaded from Google Fonts. Icons and illustrations are original inline SVG.

== Changelog ==

= 1.6.0 =
* 15 new guide posts (5 per country), scheduled one every other day after the existing queue, rotating Pakistan, India and Bangladesh, each with internal links and links to official sources.
* Scheduling continues after the last scheduled post when more content is added later.
* New cover design with a short topic label; existing posts get their cover (or the new version of a theme cover) on "Create missing".
* "Related guides" section under every post.
* Tariff alerts box on inner pages now has proper spacing instead of touching the content above.

= 1.5.0 =
* Featured images: branded cover for every guide post, attached by the importer and used on cards, posts, social shares and schema; card images now 16:9.
* Comments disabled site-wide (forms, lists, pingbacks, feeds, REST, admin screens).
* Footer: guides column removed; new bottom bar with copyright, legal links and a "Made with love by Vyntic Studio" credit (editable in the Customizer).
* Home guides grid no longer repeats a post as a placeholder.

= 1.4.0 =
* Header menu now links to real pages, with dropdowns per country; Guides opens the country's guide archive; "Check my bill" opens the country bill calculator.
* New footer: every page for the visitor's country, latest guides, company and legal links; outline wordmark removed.
* Company and legal pages: About us, How we check rates, Contact us, Privacy policy, Terms of use, Disclaimer, Cookie policy.
* Built-in tariff alerts: stored subscribers, double opt-in, one-click unsubscribe, automatic new guide emails per country, manual alerts, CSV export, automatic clean-up.
* Contact form with saved messages and email notification.
* Favicon set (SVG, ICO, PNG, Apple touch icon, manifest) and /favicon.ico redirect.
* Secondary keywords for every page and post; richer schema (Organization with logo and contact, AboutPage, ContactPage, CollectionPage, keywords, country).
* Title separator changed from a dash to a bar; country guide archives get titles, descriptions and schema.

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
