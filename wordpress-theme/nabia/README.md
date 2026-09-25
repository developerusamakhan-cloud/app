# Nabia - WordPress theme for nabiakhan.com

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

## Pages and templates

Create each page yourself in **Pages → Add New**, give it any title, and choose the template in the sidebar (**Page Attributes / Template**). Every template fills the page automatically. Anything you type in the page editor appears as an extra section.

| Page to create | Template to choose | What the page shows |
| --- | --- | --- |
| Home | none (automatic) | Set in Settings → Reading → "A static page". The homepage design is used automatically. |
| Services | **Services overview** | All 9 services, platforms, process, pricing, free audit |
| Web Design | **Service: Web Design** | Full service page (see below) |
| WordPress Development | **Service: WordPress Development** | Full service page |
| Shopify & WooCommerce | **Service: Shopify & WooCommerce Stores** | Full service page |
| Wix, Webflow & Squarespace | **Service: Wix, Webflow & Squarespace** | Full service page |
| Custom Websites | **Service: Custom-Coded Websites** | Full service page |
| AI Website Solutions | **Service: AI Website Solutions** | Full service page |
| Branding & Graphic Design | **Service: Branding & Graphic Design** | Full service page |
| Speed & SEO | **Service: Speed & SEO** | Full service page |
| Monthly Website Maintenance | **Service: Care & Maintenance (Monthly Maintenance)** | Full service page with the Basic / Standard / Premium maintenance prices |
| Pricing | **Pricing** | Website packages and maintenance plans (tabs), FAQ, free audit |
| Free Website Audit | **Free audit** | Audit form, video reviews, reviews |
| About | **About me** | Intro video and story, skills, platforms, process, video reviews, reviews |
| Hire Me / Contact | **Contact / Hire me** | Contact form (saved in Dashboard, Submissions), Fiverr & Upwork, process, FAQ |
| Portfolio (e.g. your "My works" page) | **Portfolio** | All your Websites with instant category filters, "Show more", reviews |
| Sitemap | **Sitemap** | Every page, service, project and article in one list, plus a link to the XML sitemap |
| Any page built with Elementor | **Full width (builder friendly)** | Only your Elementor content between the header and footer |

A full service page contains: headline and intro, "What you get", 6 benefits, "Perfect for", the process, the matching prices, video reviews, an FAQ, the free audit form and links to the other services.

Tips:
- Homepage service cards and the menu link to your pages automatically once they use these templates.
- Your existing `/monthly-website-maintenance/` and `/hire-me/` pages can simply switch to the matching template. They keep their address.
- Portfolio pages need no template: `/websites/` and each Website post are automatic.
- Prefer one click? **Appearance → Nabia Setup** can create all these pages and a menu for you. It never touches existing pages.

## Contact form and submissions

- The **Contact / Hire me** template shows a full contact form: name, email, phone/WhatsApp, service, budget (clickable chips) and message. Use `[nabia_contact_form]` to place it anywhere else.
- Every message is saved in **Dashboard → Submissions → Contact messages**, with a red unread counter in the menu and a New/Read status. Open a message to see all details and reply by email. **Audit requests** are listed in the same menu.
- **Submissions → Form settings**: who receives the emails, email subject, form title and intro, which optional fields to show, budget options, button text, thank-you message, privacy note, and an automatic reply to the sender (use `{name}` for their name).
- Spam protection: hidden honeypot field, rejects forms sent in under 3 seconds, and max 5 messages per hour per visitor.

## What's new in 2.13.3

- **Google Chat popup.** Every Google Chat button (contact form, after sending a form, mobile menu, footer and homepage contact) opens a small popup that shows the chat email with a Copy button, a "Copy and open chat" button and three simple steps. The WhatsApp number and chat email are no longer printed on the buttons themselves.

## What's new in 2.13.1

- **Mobile menu** items are smaller and tidier, with thin dividers, a one column submenu, and WhatsApp and Google Chat buttons at the bottom.
- **Portfolio on phones** is a proper two column grid on the Portfolio page and the Websites archive, with no sideways scrolling.
- **Blog page header** shows an author card (photo, articles, experience and an "Ask me a question" button) instead of categories. Blog cards show the reading time instead of the category.
- **FAQ** icon turns white on violet when a question is open or hovered.
- **Fiverr and Upwork cards** on phones put the logo and arrow on top and the note on its own line.

## What's new in 2.13

- **Contact form delivery fixed.** The forms now post to the page they are on instead of `/wp-admin/admin-post.php`, which security plugins and hosts often block for visitors. Pages served from a cache fetch fresh security fields when they load, so an old cached copy of the form no longer rejects real messages (every check still runs). After sending, visitors now stay on the same page instead of being sent to the homepage.
- **Form health.** Submissions, Form settings shows the last 40 form attempts with the result (saved and sent, email failed with the server's reason, wrong math answer, expired, too many, bot field filled). A "Send a test email" button checks if your server can send email. If it fails, install an SMTP plugin such as WP Mail SMTP or FluentSMTP. Messages are always saved under Submissions, even when the email fails.
- **WhatsApp and Google Chat.** New settings in Customize, Nabia Theme, Contact: WhatsApp number (preset to +92 312 1305032, with a ready typed first message) and Google Chat email (preset to devnabiakhan@gmail.com). The Google Chat button opens Google Chat and copies the email so visitors can paste it to start a chat. Both buttons appear on the contact form, after sending the contact or audit form ("Want an answer even faster?"), in the footer call to action, in the footer "Say hello" links and in the homepage contact section. A floating WhatsApp button shows on every page.
- **Audit Requests** have their own admin menu, and both lists show 20 items per page.

## What's new in 2.12

- **Author photo everywhere.** New Customizer setting (Nabia Theme, About, "Author photo"), preset to Nabia's photo. It replaces the Gravatar for every registered user, so it shows in blog post bylines, the author box, comments by the site owner and the admin bar. The homepage About section also uses it when no intro video or About image is set.
- **No filters on the portfolio.** The category pills are gone from the Portfolio page and the Websites archive. All projects show in one grid with "Show more".
- **Websites are noindex.** Single websites, the Websites archive and its category pages send `noindex, follow` (robots meta, Yoast SEO, Rank Math and an `X-Robots-Tag` header). The Portfolio page itself stays indexable.

## What's new in 2.11

- **Websites link straight to the live site.** Every portfolio card (home, portfolio page, archive, related work, HTML sitemap) opens the URL saved in the ACF field `live_link` in a new tab. Opening a single website URL redirects to that live link, or to the portfolio page when no link is set. There are no detail pages for now, so websites are also kept out of the XML sitemaps (WordPress core and Yoast). To bring detail pages back later, add `add_filter( 'nabia_redirect_projects', '__return_false' );` and point the cards back to the permalink.
- **Math captcha on the contact form.** Visitors answer a small sum like "5 + 8 =" before sending. The answer is never in the page: only a signed hash is sent with the form, and a wrong answer shows a friendly message. It works alongside the honeypot, time check and rate limit.
- **AI FAQs.** Five new homepage FAQs explain how AI is used (as an assistant, never a replacement), speed and cost, human sounding content, data safety and what a chatbot can do. The AI Website Solutions service page has four more (tools, running costs, AI blog content, the human touch).

## What's new in 2.10

- Blog page: plain grid of posts (no category buttons, no big featured post).
- Blog post breadcrumb: Home / Blog / post title (no category).
- Website packages show a crossed-out old price like the maintenance plans: $600 → $499, $999 → $799, $1499 → $1199, each with a "Save %" badge. Edit them in Customize, Nabia Theme, Pricing with a `Was: $600` line.
- Branded scrollbar (slim violet-to-pink thumb).
- Smooth scrolling with Lenis (bundled, MIT licence). It is off for visitors who prefer reduced motion, pauses while the mobile menu is open, and in-page links glide to their section. Turn it off in Customize, Nabia Theme, General.

## What's new in 2.9

- The header "Let's talk" button (and every "Hire me" / "Contact" link) goes to your page using the **Contact / Hire me** template. Order: custom link in Customize, then your Contact template page, then /hire-me/ or /contact/, then the homepage contact section.
- Menu items with a dropdown show a small arrow that flips open. The mobile menu shows the dropdown items too.
- Footer links now go to your real pages: all 9 service pages, Home, Services, Portfolio, Pricing, About, Blog, Free audit and Contact, plus Fiverr, Upwork, Sitemap and Privacy policy (when set in Settings, Privacy). To choose your own "Explore" links, assign a menu to the Footer location in Appearance, Menus.
- New **Portfolio** page template: when a page uses it, every "Work", "All projects" and "Portfolio" link goes to that page.
- New **Sitemap** page template. The footer "Sitemap" link goes to it; without it, the link goes to your XML sitemap (Rank Math / Yoast `sitemap_index.xml`, otherwise WordPress `wp-sitemap.xml`).

## What's new in 2.8

- Tick icons are drawn as icons, centred on the first line of text in every list (audit, pricing, "What you get", checklists).
- Single blog post: content, author box, navigation and comments share one column and edge; readable width; images fill the column; smaller in-article headings.
- Reviews: 6 shown by default in an equal-height grid, 3 lines each with "Read more", plus "Show more reviews".
- New contact form with WordPress submissions and a settings page (see above).

## What's new in 2.7

- **Same header on every inner page**: breadcrumbs, label, title, intro and a visual on the right that fits the page (topics card on the blog, author card on posts, project details on websites, stats on Services, "what's included" on Pricing, profile card on About, a playful 404 and more).
- **New blog overview**: category filters, a large featured latest post and a "Browse topics" card.
- **New single blog post**: automatic "On this page" contents, a sticky "Need help?" card, author box, related articles and the free audit form.
- **New single website page**: project details card, "Visit live site" button, screenshot in a browser frame, related projects, free audit and next project.
- **New 404 page**: animated 404, search, quick links and latest articles.
- **Internal linking everywhere**: service pages show recent work and helpful articles; posts and projects show related content; words like "Shopify", "SEO", "website maintenance" or "WordPress" in your blog posts and project descriptions automatically link to the matching service page (the first mention only, at most 4 links per post).
- **Free audit form**: now just website URL + email. It appears on the homepage, website service pages, single posts, single websites and the Free audit page, but not on Branding, About, Pricing or Contact.
- **"Why work with me" band**: four reasons with icons, shown on several pages for variety.
- Dark sections never sit next to each other any more; the Services overview no longer repeats its title; tick icons line up with their text.

## What's new in 2.6

- Monthly maintenance prices: Basic $45.99 (was $69.99), Standard $79.99 (was $100), Premium $159.99 (was $200, marked "Most popular"). Each shows the crossed-out old price, a "Save %" badge, the days per month and the support note.
- One template per page (see the table above), so you can create pages yourself and just pick a template.

## What's new in 2.5

- **Pricing section** on the homepage with two tabs:
  - **Websites**: New Startup $499, Business Website $799 (highlighted as "Most popular"), E-Commerce Store $1199.
  - **Monthly maintenance**: Basic, Growth and Premium Care. These show "Custom quote" until you add your monthly prices.
  - Edit every plan in Customize, Nabia Theme, Pricing. Line 1 is the name, line 2 the price, then one feature per line; start a line with "-" to mark it as not included. "Hire me" buttons go to your /hire-me/ page if it exists, otherwise to the contact section; you can also set a custom link.
- **Free website audit section**: a form (website, name, email, goal, message). Each request is emailed to your contact email and saved under Dashboard, Audit Requests, with spam protection. To use Contact Form 7 or WPForms instead, paste its shortcode in Customize, Nabia Theme, Free Audit.
- **Service pages**: Appearance, Nabia Setup creates a Services page, 9 detailed service pages, a Pricing page and a Free Website Audit page in one click, plus a main menu with a Services dropdown. Each service page has its own headline, intro, "What you get", 6 benefits, "Perfect for", process, pricing, video reviews, FAQ, the free audit form and links to the other services. The homepage service cards link to these pages automatically.
- Upwork profile link added below Fiverr.

## What's new in 2.4

- **New logo**: an animated "Nabia Khan" wordmark with no tagline, in the header and footer. The dot on the "i" bounces, "Khan" has a gradient, and an underline draws in on hover. To use an uploaded logo image instead, tick the option in Customize, Nabia Theme, General.
- **Social links**: a single Linktree button ("All my links") replaces all social icons.
- **Fiverr and Upwork** are stacked, Upwork below Fiverr. Add your Upwork link in Customize, Nabia Theme, Links.
- **More content**: 9 services, now including Shopify, Wix/Webflow/Squarespace, custom-coded sites and AI website solutions (chatbots, AI content, automation). A new "Platforms" section and new FAQ answers about platforms and AI.
- Video section: the review count and YouTube button are removed.
- No long dashes anywhere in the site text.

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

- Smooth scrolling: [Lenis](https://github.com/darkroomengineering/lenis) 1.3.26, MIT licence (see `assets/js/vendor`).
- Fonts: [Plus Jakarta Sans](https://github.com/tokotype/PlusJakartaSans) and [Inter](https://rsms.me/inter/), SIL Open Font License 1.1 (see `assets/fonts`).
- License: GPL v2 or later.
