# Nabia Khan: Brand Guide for Designers and AI Tools

Use this guide to create thumbnails, social posts, banners and other graphics that match nabiakhan.com. If you are an AI assistant reading this: follow every rule below exactly, and look at the images in `examples/` before designing.

## 1. Who the brand is
- **Name:** Nabia Khan (write it exactly like this). Website: **nabiakhan.com**
- **What:** web designer and developer. Custom websites, WordPress, Shopify and WooCommerce stores, church and nonprofit websites, AI website solutions (chatbots, automations), speed and SEO, monthly website care.
- **Audience:** small business owners, churches and nonprofits, mainly in the US.
- **Tagline:** "Websites built for the AI era." (also used: "Websites that stand out.")
- **Personality:** modern, confident, friendly, trustworthy. Premium but approachable. Never salesy or loud.

## 2. Colours
| Name | Hex | Use |
|---|---|---|
| Ink | `#1A1433` | Main dark background, text on light backgrounds |
| Violet | `#7C3AED` | Main brand colour, buttons, glows |
| Pink | `#EC4899` | Accent, second half of gradients |
| Gold | `#FBBF24` | Highlights, "why it matters" text, small labels on dark |
| Lilac | `#B9A4FF` | Soft accent on dark backgrounds |
| Soft background | `#FBFAFF` | Light slide backgrounds |
| Green (ticks only) | `#34D399` | Check-mark circles on dark backgrounds |

**Gradients**
- Buttons and accents on light or dark: Violet to Pink, `linear-gradient(100deg, #7C3AED, #EC4899)`
- Highlighted words on dark backgrounds: Lilac to Pink to Gold, `linear-gradient(100deg, #B9A4FF, #EC4899 55%, #FBBF24)`
- Highlighted words on light backgrounds: Violet to Pink

## 3. Fonts (free Google Fonts, files in `fonts/`)
- **Headings and big words:** Plus Jakarta Sans, ExtraBold 800, tight letter spacing (about -0.04em), line height about 1.0
- **Body text, labels, buttons:** Inter, Regular 400 and SemiBold 600
- Never use more than these two fonts.

## 4. The signature look (copy this style)
1. **Dark Ink background** (`#1A1433`) for covers, thumbnails and calls to action.
2. **Two big blurred glows**: a Violet glow in the top right (about 55% opacity) and a Pink glow in the bottom left (about 30% opacity), both heavily blurred so they look like soft light.
3. **A faint grid** over the background: thin white lines at 5% opacity, every 72 to 80 px.
4. **One or two key words in the headline use the gradient text** (for example "losing" in "Is your website quietly *losing* customers?").
5. **Pills**: small rounded labels with a light transparent fill and thin border, uppercase, letter-spaced (for example "WEBSITE CARE", "FREE GUIDE").
6. **Buttons**: fully rounded, Violet to Pink gradient, white bold text, often with an arrow (→).
7. **Check lists**: round green or gradient circles with a ✓, white bold text.
8. **White cards** with rounded corners (24 to 30 px) and a soft shadow for lists on light slides.
9. **Logo** at the top left (white version on dark, dark version on light) and **nabiakhan.com** at the bottom.
10. Lots of breathing room. Big text, few words.

Light variation (inside carousel slides): Soft background `#FBFAFF` with a Lilac glow top right, a big gradient number (01, 02...), dark Ink headline, white rounded cards, and a dark Ink "Why it matters" box with Gold text.

## 5. Logo
- Files in `logos/`: `logo` (icon + name), `wordmark` (name only), `logo-mark` (the "N" icon). Each has a white version for dark backgrounds. SVG and PNG.
- The icon: a white rounded "N" on an Ink rounded square, with a small Violet to Pink dot at the bottom right.
- Do not stretch, recolour, outline or add effects. Keep clear space around it.
- Icon as code (paste into any SVG/HTML design):

```svg
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="512" height="512" role="img" aria-label="Nabia Khan"><defs><linearGradient id="d" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7c3aed"/><stop offset="1" stop-color="#ec4899"/></linearGradient></defs><rect x="1" y="1" width="46" height="46" rx="15" fill="#1a1433"/><path d="M15 34V15.5a1.5 1.5 0 0 1 2.6-1l12.8 15a1.5 1.5 0 0 0 2.6-1V14" fill="none" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="36.5" cy="36" r="4" fill="url(#d)"/></svg>
```

## 6. Writing rules (very important)
- **English only** on all graphics and captions.
- **Never use long dashes** (no em dash or en dash). Use a comma, colon, full stop or the word "to" instead (write "10 to 12", not a dash).
- Short, plain, benefit-first headlines. Speak to the reader as "you". Question hooks work well: "Is your website quietly losing customers?"
- No hype or fake claims ("#1", "guaranteed", made-up numbers or reviews).
- Common calls to action: "Free website audit →", "Link in bio", "Save this for later", "nabiakhan.com".

## 7. Sizes
| Format | Size (px) |
|---|---|
| YouTube thumbnail | 1280 x 720 |
| Instagram / Facebook post or carousel | 1080 x 1350 |
| Square post | 1080 x 1080 |
| Reel, Short, Story, reel cover | 1080 x 1920 (keep text out of the top 250 and bottom 400 px) |
| Pinterest pin | 1000 x 1500 |
| LinkedIn company banner | 1128 x 191 (keep the left 280 px empty: the logo covers it) |
| LinkedIn personal banner | 1584 x 396 |
| Blog cover / share image | 1200 x 630 |

## 8. Thumbnail recipe (YouTube, blog, video covers)
- Ink background + Violet and Pink glows + faint grid.
- 3 to 6 words maximum in Plus Jakarta Sans ExtraBold, very large, white, with one gradient word.
- Optional: one simple visual on the right (phone or laptop mockup, browser window, icon, or Nabia's photo with a soft glow behind).
- Small pill label on top (topic), logo or "nabiakhan.com" small in a corner.
- High contrast; readable when shrunk to a phone screen.

## 9. Examples (in `examples/`)
- `carousel-cover-dark.png`: dark cover slide with gradient headline and pill
- `carousel-inside-light.png`: light inside slide with number, cards and "Why it matters" box
- `carousel-cta-dark.png`: dark call-to-action slide with check list and gradient button
- `reel-cover-9x16.jpg`: vertical reel cover
- `pinterest-pin.png`: pin with blog cover image
- `linkedin-banner.png`: LinkedIn company banner
- `template/banner-example.html`: a working HTML/CSS example of the style (open in a browser)

## 10. CSS tokens (for anyone building in HTML)
```css
:root{
  --ink:#1a1433; --violet:#7c3aed; --pink:#ec4899; --gold:#fbbf24; --lilac:#b9a4ff; --soft:#fbfaff; --tick:#34d399;
  --grad:linear-gradient(100deg,#7c3aed,#ec4899);
  --grad-text-dark:linear-gradient(100deg,#b9a4ff,#ec4899 55%,#fbbf24);
  --font-head:"Plus Jakarta Sans",sans-serif; /* 800 */
  --font-body:"Inter",sans-serif; /* 400, 600 */
}
```
