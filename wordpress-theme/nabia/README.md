# Nabia — WordPress theme for nabiakhan.com

A bold, animated one-page portfolio theme for a WordPress developer & graphic designer.
It uses the style of creative agency sites (big type, bright accent colour, playful motion) and adds more interaction.

## Features

- **Hero** with a word that changes every few seconds and an "available" badge. The visual is a live website mockup that tilts in 3D, with a PageSpeed gauge, a 5-star review card, a growing "+42% leads" chart and a "Nabia" cursor that moves around as if designing. Upload a portrait to show it inside the frame instead.
- **Stats strip**: four animated counters on a dark bar; each card lights up on hover
- **Intro preloader** that counts from 0 to 100
- **Video reviews** from YouTube: a big player that starts playing (muted) when visitors scroll to it, a "Tap for sound" button and a playlist of other reviews. Shorts links show as a row of tall vertical videos. The section is hidden until you add videos.
- **New logo**: an "N" monogram (also used as the browser tab icon) with the name and a small tagline
- **5 colour schemes**: Coral & Indigo (default), Electric Lime, Mint & Ocean, Lavender & Tangerine, Sunshine & Pink, plus an optional custom accent colour
- **Promotional footer**: a large scrolling "Let's work together" slogan, a call-to-action with a spinning "Start a project" button, services and contact columns, and a giant name that fills with colour as it scrolls into view
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
| General | Brand name, logo tagline, colour scheme, custom accent colour, preloader on/off, custom cursor on/off, footer text, footer slogan and call-to-action |
| Hero | Badge, headline, rotating words (comma separated), intro, buttons, portrait photo |
| Marquee & Stats | Scrolling skill words, the four numbers and labels |
| About & Section Titles | About text and photo, titles of every section |
| Video Reviews (YouTube) | Title, intro, your video links, layout (automatic / wide / vertical) |
| Contact | Email, WhatsApp number, contact-form shortcode, client-portal link |
| Social Links | LinkedIn, Instagram, Behance, Dribbble, GitHub, YouTube, Upwork, Fiverr |

### Adding your YouTube reviews

In **Customize → Nabia Theme → Video Reviews**, paste one video per line:

```
https://youtu.be/VIDEO_ID | Sarah Malik, Bloom Botanics | New store in 2 weeks
https://www.youtube.com/watch?v=VIDEO_ID | James Carter | Website redesign
https://youtube.com/shorts/VIDEO_ID | Ayesha R. | Branding + website
```

The name and caption are optional. Normal links, youtu.be links and Shorts links all work. Videos load only when visitors scroll to them, so they don't slow down the page, and they use YouTube's privacy-enhanced (no-cookie) player.

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
