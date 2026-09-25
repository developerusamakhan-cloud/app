# Nabia — WordPress theme for nabiakhan.com

A bold, animated one-page portfolio theme for a WordPress developer & graphic designer.
It uses the style of creative agency sites (big type, bright accent colour, playful motion) and adds more interaction.

## Features

- **Hero** with a word that changes every few seconds, an "available" badge, a tilting 3D card, floating tags and a spinning badge that links down the page
- **Animated counters** (years, projects, …) and an **intro preloader** that counts from 0 to 100
- **Skills marquee**: an endless, tilted scrolling strip
- **Services**: cards that fill with colour from wherever the cursor enters
- **Portfolio**: a *Projects* post type with categories, a filterable archive at `/work/`, case-study pages and a big "Next project" link
- **About** section with animated skill bars
- **Process** steps, a **testimonials slider** you can drag (*Testimonials* post type), and a **FAQ** accordion
- **Contact** card with a glow that follows the cursor, optional contact-form shortcode, WhatsApp button and social links
- Custom cursor, magnetic buttons, scroll-reveal animations, a scroll-progress bar, and a header that hides while scrolling down
- Full-screen animated mobile menu
- Self-hosted fonts (Syne + Inter): no Google Fonts requests and GDPR-friendly
- Respects "reduced motion" settings; content stays visible without JavaScript
- Blog, single post (with reading time), page, search, 404 and a *Full width* template for Elementor/block-editor pages

## Install

1. Download `nabia.zip`.
2. In WordPress go to **Appearance → Themes → Add New → Upload Theme**, choose the zip and click **Activate**.
3. Go to **Settings → Permalinks** and click **Save** once (this makes `/work/` URLs work).

## Make it yours

Everything is under **Appearance → Customize → Nabia Theme**:

| Section | What you can change |
| --- | --- |
| General | Brand name, accent colour, preloader on/off, custom cursor on/off, footer text |
| Hero | Badge, headline, rotating words (comma separated), intro, buttons, portrait photo |
| Marquee & Stats | Scrolling skill words, the four numbers and labels |
| About & Section Titles | About text and photo, titles of every section |
| Contact | Email, WhatsApp number, contact-form shortcode, client-portal link |
| Social Links | LinkedIn, Instagram, Behance, Dribbble, GitHub, YouTube, Upwork, Fiverr |

Also:

- **Projects → Add New**: title, description, featured image (the thumbnail), *Excerpt* (subtitle) and the *Details* box (client, year, services, live URL). Drag order via *Order* in Page Attributes.
- **Testimonials → Add New**: title = client name, content = quote, featured image = avatar, *Details* = role and rating.
- **Appearance → Menus**: assign a *Primary* and *Footer* menu. Until you do, the menus link to the homepage sections (`#services`, `#work`, `#about`, `#process`, `#contact`).
- **Logo**: Customize → Site Identity → Logo (otherwise the brand name is shown as text).

Until you add real Projects and Testimonials, placeholder cards are shown. Only logged-in editors see the hint telling you to replace them.

> **Please check before going live:** the default stats (250+ projects, 100% job success, 24h reply), skill percentages and two of the three placeholder testimonials are sample content. Replace them with your real numbers and client reviews.

## For developers

Services, process steps, FAQ and skills can be changed with filters in a child theme:

```php
add_filter( 'nabia_faq', function ( $faq ) {
	$faq[] = array( 'q' => 'Do you offer hosting?', 'a' => 'Yes, managed hosting is available.' );
	return $faq;
} );
```

Available filters: `nabia_services`, `nabia_process`, `nabia_faq`, `nabia_skills`.

## Credits

- Fonts: [Syne](https://gitlab.com/bonjour-monde/fonderie/syne-typeface) and [Inter](https://rsms.me/inter/), SIL Open Font License 1.1 (see `assets/fonts`).
- License: GPL v2 or later.
