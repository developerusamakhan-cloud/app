=== PowerBachat ===
Requires at least: 6.2
Tested up to: 6.8
Requires PHP: 7.4
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
(NEPRA for Pakistan, TNERC / KSERC for India, BERC for Bangladesh) before going live.
After checking, update "Tariffs last checked" in the Customizer so the date shown on
the site is honest.

The solar panel price board is edited in Appearance → Customize → PowerBachat →
Rates & prices. Until you save your own rows, admins see a reminder under the board.

== Home page ==

Sections (in order): hero + live bill calculator, rate ticker, utility directory,
slab chart, solar sizing tool, price board, guides, method, FAQ, alerts sign-up.
Reorder or remove them with the `powerbachat_home_sections` filter.

The country switch (PK / IN / BD) in the top bar changes the calculator, chart,
solar tool and utility list together, and is remembered on the visitor's device.

== Building the calculator pages ==

The utility list in inc/data.php already holds the URLs from the keyword plan, e.g.
/pk/lesco-bill-calculator/, /in/tneb-bill-calculator/, /bd/desco-bill-calculator/.

1. Create a parent page with slug `pk` (or `in`, `bd`).
2. Create a child page with slug `lesco-bill-calculator`.
3. Page Attributes → Template → "Calculator page".

The template detects the company from the URL, shows the calculator pre-set to it,
your page content underneath, then the slab chart. For a URL that is not in the list,
add custom fields `pb_country` (pk|in|bd) and `pb_utility` (e.g. lesco).

India companies without tariff data yet (UPPCL, MSEDCL, BESCOM, Delhi) appear in the
directory but not in the calculator dropdown. Add a tariff table for them in
inc/data.php and set their `tariff` key to switch them on.

== Shortcodes ==

[powerbachat_calculator country="pk" utility="iesco" units="300"]
[powerbachat_slab_chart country="bd"]
[powerbachat_solar country="in" units="400"]

Leave `country` empty to follow the visitor's country switch.

== Customizer (Appearance → Customize → PowerBachat) ==

* Home page: default country, hero headline (wrap words in *asterisks* for the
  hand-drawn underline), hero intro.
* Rates & prices: tariffs-checked date, price board rows, currency, update date.
* Alerts & footer: newsletter form action URL (Mailchimp, Brevo…), WhatsApp channel
  URL, footer disclaimer.

== Credits ==

Fonts: Fraunces, Hanken Grotesk, JetBrains Mono, Noto Nastaliq Urdu (SIL Open Font
License), loaded from Google Fonts. Icons and illustrations are original inline SVG.
