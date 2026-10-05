# TheDIGF.com: Elementor templates and Global Style Kit

The two HTML pages (`index.html` and `privacy/index.html`) were rebuilt as Elementor templates. They use only
**Flexbox Containers** and native widgets: Heading, Text Editor, Button, Image, Icon, Icon List,
Nested Tabs and Nested Accordion. None of the text sizes or colours are hard-coded in the pages. Every widget reads
from the **Global Colours** and **Global Fonts** in the kit, so a change in Site Settings updates the whole site.

## Files

| File | What it is | Where it is imported |
|---|---|---|
| `thedigf-global-style-kit.zip` | Global Style Kit: global colours, global fonts, theme styles (body, links, H1–H6), buttons, images, form fields, layout (1220px content width), page background | **Elementor › Tools › Import / Export Kit**, under *Import a Template Kit* |
| `thedigf-home.json` | Home page. The discovery form uses the **Elementor Pro Form** widget, which emails `Reggie@TeleTechTX.com` | **Templates › Saved Templates › Import Templates** |
| `thedigf-home-free-form.json` | The same Home page for sites **without Elementor Pro**. The form is a Shortcode widget, so you can paste in a Contact Form 7 or WPForms shortcode | **Templates › Saved Templates › Import Templates** |
| `thedigf-privacy.json` | Privacy page | **Templates › Saved Templates › Import Templates** |
| `tools/build.py` | The script that generated all the files above. Change a value and run `python3 tools/build.py` to rebuild them | none |

## Requirements

- WordPress with **Elementor 3.24 or newer**. The latest version is recommended.
- In **Elementor › Settings › Features**, set *Flexbox Container* and *Nested Elements* to **Active**. They are
  active by default on new installations.
- Elementor Pro is needed **only** if you use `thedigf-home.json` with its built-in form.
- Recommended theme: **Hello Elementor**. Its default styles do not compete with the kit.

## How to import (in this order)

### Step 1: Import the Global Style Kit first

The page templates refer to the kit's global colours and fonts by ID. If you import the kit after the pages, the pages
show unstyled text until the kit is in place.

1. In the WordPress dashboard, open **Elementor › Tools › Import / Export Kit**.
2. Under **Import a Template Kit**, click **Start Import** and upload `thedigf-global-style-kit.zip`.
3. On the content screen, keep **Site Settings** ticked and click **Import**.
4. Check the result: open any page in Elementor, click the **☰ / Site Settings** icon, then open **Global Colors** and
   **Global Fonts**. You should see entries such as *Navy*, *Gold Light (CTA)*, *H1 Display* and *Eyebrow*.

> If the kit importer is unavailable on your hosting, see *Manual setup of global settings* below.

### Step 2: Import the page templates

1. Open **Templates › Saved Templates** and click **Import Templates** at the top.
2. Upload `thedigf-home.json` (or `thedigf-home-free-form.json`) and `thedigf-privacy.json`.
3. The three photos are downloaded from `teletechtx.com` into your Media Library during import.
   If an image shows a grey placeholder, the remote server blocked the download. Upload the image manually and
   select it in the Image widget.

### Step 3: Create the pages and insert the templates

1. Go to **Pages › Add New** and create **Home**. Click **Edit with Elementor**.
2. In the editor, click the **folder icon** (Add Template), open the **My Templates** tab and click **Insert** next to
   *TheDIGF – Home*. If Elementor asks whether to apply the template's page settings, choose **Yes**.
3. Open **Page Settings** (the gear icon) and set **Page Layout = Elementor Canvas**. The template includes its own
   header and footer, so the theme's header and footer must be hidden. Click **Publish**.
4. Repeat for **Privacy** with *TheDIGF – Privacy*. Set the page **slug to `privacy`**, because the footer links to `/privacy/`.
5. Go to **Settings › Reading**, choose *A static page* and set **Homepage = Home**.

## How the page is built

```
Section container (boxed, 1220px, side padding 40 / 24 / 18 px)
 ├─ Eyebrow        → Heading widget (div)   + Global Font "Eyebrow", Global Colour "Gold Dark"
 ├─ Section title  → Heading widget (h2)    + Global Font "H2 Section"
 ├─ Paragraph      → Text Editor widget     + Global Font "Intro / Body", Global Colour "Muted Text"
 ├─ Cards          → Grid container (4 → 2 → 1 columns) of child containers
 └─ CTA            → Button widget          + Global Font "Button", Global Colours "Navy" / "Gold Light"
```

| HTML section | Elementor build |
|---|---|
| Header (logo, menu, CTA) | Row container: Heading (logo), Icon List in inline view (menu links), Button |
| Hero | Gradient container with two columns: H1, Text Editors, Button + text link, Image, and a linked card container. The ambient line animation is an optional HTML widget |
| Who we serve | Grid container, 4 / 2 / 1 columns, card containers |
| Hidden exposure (`#approach`) | Two-column row, 2-column grid of numbered cards |
| Deal lifecycle | **Nested Tabs**, which switch to an accordion on mobile |
| Scorecard (`#scorecard`) | Card container with a navy header and 7 status rows (Icon widget dot, name, finding, pill label) |
| Seven domains | **Nested Accordion** (plus / minus icons) and a callout container with a gold left border |
| How it works | Image with caption, three step rows with a round number container |
| Founder | Photo, bio, and a 2-column grid of principles |
| Engagements (`#engagement`) | 2-column pricing grid (Icon List bullets), plus a distinction grid |
| FAQ | **Nested Accordion** with *FAQ Schema* enabled, which improves SEO |
| Request discovery (`#discovery`) | Left: copy and contact links. Right: form card (Pro Form, or a Shortcode in the free version) |
| Footer | Brand, address with tel / mailto / LinkedIn links, and a bottom bar linking to `/privacy/` |

Menu anchors (`#approach`, `#scorecard`, `#engagement`, `#discovery`, `#main`) are set as **CSS ID** values on the
section containers (*Advanced › CSS ID*), so the header links scroll to the right section.

Responsive layout: each multi-column row stacks on mobile. Font sizes step down on tablet and mobile through the
global fonts. On mobile the header shows only the logo; the menu links and the header button are hidden, and the hero button is the main call to action.

## Global Colours (in the kit)

| Global colour | Hex |
|---|---|
| Primary (system) | `#142E40` |
| Secondary (system) | `#D5AE65` |
| Text (system) | `#5D6E78` |
| Accent (system) | `#102E43` |
| Navy | `#102E43` |
| Deep Navy | `#092235` |
| Night (Hero / Header) | `#071E30` |
| Ink (Headings) | `#142E40` |
| Muted Text | `#5D6E78` |
| Gold | `#D5AE65` |
| Gold Light (CTA) | `#DFBD7A` |
| Gold Hover | `#EED2A0` |
| Gold Dark (Eyebrow) | `#8C6B34` |
| Cream (Page BG) | `#F7F5EF` |
| Sand | `#E8E1D2` |
| Stone (Callout BG) | `#EEECE5` |
| Line / Border | `#D9DFDF` |
| Paper | `#F5F7F7` |
| White | `#FFFFFF` |
| Text on Dark | `#B9CBD7` |
| Lead on Dark | `#E0EAF0` |
| Navy Hover | `#244D65` |
| Status Green | `#30896F` |
| Status Yellow | `#C99735` |
| Status Red | `#BC5C5E` |

## Global Fonts (all Inter)

| Global font | Desktop / Tablet / Mobile | Weight | Notes |
|---|---|---|---|
| Primary (system) | 50 / 40 / 34 px | 600 | line height 1.14 |
| Secondary (system) | 18 / 18 / 16 px | 600 | |
| Text (system) | 16 px | 400 | line height 1.65 |
| Accent (system) | 14 / 14 / 13 px | 600 | |
| H1 Display | 66 / 52 / 40 px | 600 | letter spacing −3.4px |
| H2 Section | 50 / 40 / 34 px | 600 | letter spacing −2.5px |
| H3 Card | 18 / 18 / 16 px | 600 | |
| H3 Large | 30 / 28 / 26 px | 600 | tab panels, founder name |
| Eyebrow | 11.5 / 11.5 / 10.5 px | 600 | UPPERCASE, letter spacing 1.4px |
| Lead | 17.5 / 17.5 / 16 px | 400 | |
| Intro / Body | 16 / 16 / 15 px | 400 | line height 1.8 |
| Small | 14.5 / 14.5 / 14 px | 400 | |
| Extra Small | 12.5 / 12.5 / 12 px | 400 | |
| Stat Number | 29 / 29 / 24 px | 600 | |
| Price | 27 / 24 / 24 px | 600 | |
| Brand / Logo | 25.6 / 25.6 / 21.6 px | 700 | |
| Button | 14 / 14 / 13 px | 600 | |
| Emphasis | 14 px | 500 | accordion titles, tab titles |

**Theme Style** (also in the kit): body text Inter 16px in Ink, links Ink with a Gold Dark hover, and H1–H6 in Inter 600.
Buttons default to a Navy background, white text, 7px radius and 17 × 20px padding, with a Navy Hover background.
Form fields use a `#D3DCDC` border, 6px radius and a gold border on focus. Layout uses a 1220px content width,
0 default container padding and a Cream body background.

## Manual setup of global settings (only if the kit import is unavailable)

Open any page in Elementor, then **☰ › Site Settings**:

1. **Global Colors**: add the colours from the table above. Use the same names.
2. **Global Fonts**: add the fonts from the table above.
3. **Layout**: set *Content Width* to `1220px` and *Container Padding* to `0`.
4. **Background**: set *Color* to `#F7F5EF`.
5. **Typography / Buttons / Form Fields**: enter the values listed under *Theme Style* above.

Note: global settings created by hand get new internal IDs, so the imported templates will not link to them
automatically. The kit import is the reliable route. Use the manual route only for reference or for new pages.

## Notes and options

- **Form (Pro version):** after inserting the page, open the Form widget, go to *Actions After Submit › Email* and
  check the *To* address and the subject. To add a booking link after submission, add a *Redirect* action.
- **Form (free version):** create a form in Contact Form 7 or WPForms with the fields *Full name, Firm name, Role,
  Number of portfolio companies or locations, Email, Phone*. Then replace the placeholder shortcode in the Shortcode
  widget with the plugin's shortcode.
- **Sticky header:** sticky positioning is an Elementor Pro *Motion Effects* feature. With Pro, select the header
  container and go to *Advanced › Motion Effects › Sticky: Top*.
- **Hero animation:** the slow gold and blue line animation from the original page lives in the HTML widget
  *Hero motion (optional)* at the end of the Hero section. Delete that widget to turn the animation off. It respects
  *prefers-reduced-motion*.
- **Not carried over:** the scorecard filter buttons, the click-to-expand scorecard rows, the reading-progress bar and
  the mobile sticky CTA were JavaScript-only. The scorecard now shows every finding at once. Scroll-reveal is
  replaced by Elementor's *Fade In Up* entrance animation on the card grids.
