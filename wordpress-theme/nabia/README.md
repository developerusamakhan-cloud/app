# Nabia — WordPress theme for nabiakhan.com

A bold, animated one-page portfolio theme for a WordPress developer & graphic designer.
It uses the style of creative agency sites (big type, bright accent colour, playful motion) and adds more interaction.

## Features

- **Hero** with a word that changes every few seconds and an "available" badge. The visual is a live website mockup that tilts in 3D, with a PageSpeed gauge, a 5-star review card, a growing "+42% leads" chart and a "Nabia" cursor that moves around as if designing. Upload a portrait to show it inside the frame instead.
- **Stats strip**: four animated counters on a dark bar; each card lights up on hover
- **Intro video** in the About section: plays muted while visible, pauses when scrolled away, "Tap for sound" button, plus a "Watch my intro" link in the hero. Set the MP4 link, cover image and caption in Customize → Nabia Theme → About & Section Titles.
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
- Self-hosted fonts (Plus Jakarta Sans + Inter): no Google Fonts requests and GDPR-friendly
- Respects "reduced motion" settings; content stays visible without JavaScript
- Blog, single post (with reading time), page, search, 404 and a *Full width* template for Elementor/block-editor pages

## What's new in 2.3

- **Self-hosted video reviews**: your 5 client videos (Annika Dose, Dr Craig Duncan, Phillip Duff, Reginald Hilliard, Tara Lori) play straight from your Media Library, with no YouTube involved. Names are read from the file names. A video downloads only when visitors reach the section, plays muted, and pauses when scrolled away; "Tap for sound" turns on sound for one video and mutes the rest.
- **Websites post type built into the theme**: `?post_type=websites` and `/websites/` work without Custom Post Type UI or any other plugin, and existing categories are kept. You can deactivate that plugin; nothing is lost.
- The theme's own **Projects** menu is hidden because Websites replaces it. It only comes back if you have Projects posts or choose it as the portfolio.

## What's new in 2.2

- **Shorts carousel**: 4 client Shorts visible at a time, all playing muted. Use the arrows, dots or swipe to see more. "Tap for sound" turns on sound for one video and mutes the others. The "Happy client #…" captions are gone; a caption only shows if you give a video a name in Dashboard → Video Reviews.
- Email is now **info@nabiakhan.com**, and the client portal link is removed.
- Social links: Fiverr (fiverr.com/nabia_khan), YouTube and Linktree (linktr.ee/nabia_khan) are set. Add LinkedIn, Instagram, TikTok and others in Customize → Nabia Theme → Social Links.
- Less empty space between the hero and the skills strip; the skills strip is slimmer.

## What's new in 2.1

- The skills strip is now straight, and its stars no longer spin.
- **Client Shorts**: your 5 YouTube Shorts reviews are built in and show as a row of tall video cards. The first plays muted when visitors scroll to it; tapping another card plays that one with sound and stops the rest. Add more in Dashboard → Video Reviews, or edit the list in Customize → Nabia Theme → Video Reviews.
- **Compact portfolio**: 3 columns on desktop, 2 on tablets, a swipeable row on phones, showing 6 projects plus "All projects".
- **Fiverr / Upwork section**: a small call-to-action with a tile for each platform. It appears once you add your profile links in Customize → Nabia Theme → Social Links, where you can also edit its texts.

## What's new in 2.0

- **Cache fix**: CSS/JS URLs now change with every upload, so browsers and caching plugins never show an old, broken layout again.
- **New font**: Plus Jakarta Sans for headings (clean and professional), Inter for text.
- **New colours**: Violet & Pink by default, plus Navy & Gold, Coral & Indigo, Mint & Ocean, Electric Lime and Sunshine & Pink.
- **Your existing portfolio**: the homepage and `?post_type=websites` use your **Websites** posts (featured image, title, category, "Visit site" link). Change it in Customize → Nabia Theme → Portfolio.
- **Video Reviews menu** in the dashboard for YouTube testimonials, shown in a large player with a playlist.
- **Google reviews** fetched automatically, with a rating summary card and a review wall.
- **Intro video** in a phone frame.
- The old page-builder content of the static front page is no longer mixed into the homepage.

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

### Adding your Google reviews

1. Find your **Place ID**: open https://developers.google.com/maps/documentation/places/web-service/place-id, search your business name, and copy the ID (starts with `ChIJ…`).
2. Create an **API key**: in https://console.cloud.google.com create a project, enable **Places API (New)**, then go to *Credentials → Create credentials → API key*. Restrict the key to "Places API (New)". Google requires a billing account, and review lookups fit well inside the monthly free usage.
3. In **Customize → Nabia Theme → Google Reviews**, paste both and click **Publish**.

The section shows your average rating, total count, "Write a review" and "See all on Google" buttons, and a wall of reviews. Your **Testimonials** posts appear in the same wall. Reviews refresh every 12 hours. Google only returns up to 5 reviews per request, so the theme keeps every review it has ever received and the wall grows over time. If Google is unreachable, the last reviews keep showing. Put the section on any page with the shortcode `[nabia_google_reviews]`.

### Adding your YouTube reviews

**Dashboard → Video Reviews → Add video review**: title = client name, then paste the video link (an MP4 from Media → Library → "Copy URL", or a YouTube link), company and a short caption. Use *Order* to choose which video plays first.

Or, in the Customizer list:

In **Customize → Nabia Theme → Video Reviews**, one video per line:

```
https://youtu.be/VIDEO_ID | Sarah Malik, Bloom Botanics | New store in 2 weeks
https://www.youtube.com/watch?v=VIDEO_ID | James Carter | Website redesign
https://youtube.com/shorts/VIDEO_ID | Ayesha R. | Branding + website
```

The name and caption are optional. Normal links, youtu.be links and Shorts links all work. Videos load only when visitors scroll to them, so they don't slow down the page, and they use YouTube's privacy-enhanced (no-cookie) player.

Also:

- **Websites → Add new Website**: title, description, featured image (the thumbnail), category and *Excerpt* (subtitle). A custom field named `website_url` (or `live_url`) adds a "Visit site" button.
- **Testimonials → Add New**: title = client name, content = quote, featured image = avatar, *Details* = role and rating.
- **Appearance → Menus**: assign a *Primary* and *Footer* menu. Until you do, the menus link to the homepage sections (`#services`, `#work`, `#about`, `#process`, `#contact`).
- **Logo**: Customize → Site Identity → Logo (otherwise the brand name is shown as text).

Until you add real Projects and Testimonials, placeholder cards are shown. Only logged-in editors see the hint telling you to replace them.

> **Please check before going live:** the default stats (300+ projects, 100% job success, 24h reply), skill percentages and two of the three placeholder testimonials are sample content. Replace them with your real numbers and client reviews.

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

- Fonts: [Plus Jakarta Sans](https://github.com/tokotype/PlusJakartaSans) and [Inter](https://rsms.me/inter/), SIL Open Font License 1.1 (see `assets/fonts`).
- License: GPL v2 or later.
