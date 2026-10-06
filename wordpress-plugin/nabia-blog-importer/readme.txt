=== Nabia Blog Importer ===
Contributors: nabiakhan
Tags: import, blog, schedule, posts
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later

Import ready-made blog packs (.zip) and publish the articles now, as drafts or on a schedule.

== Description ==

Go to Posts → Blog Packs.

1. Upload a blog pack (.zip). Do not unzip it first.
2. Tick the articles you want.
3. Choose how they go live:
   * Schedule: pick the first date and time, then "one article every X days or weeks" and the weekdays allowed.
   * Publish all now.
   * Save as drafts.
4. Click Import articles.

Each article gets its cover image as the featured image (with alt text), images inside the article
(with alt text and captions), its category and tags, a FAQ section with FAQ schema, and its SEO title,
meta description and focus keyword for Rank Math or Yoast SEO.

Packs bigger than your server's upload limit can be added from the server: upload the zip by FTP
or File Manager to wp-content/uploads/nabia-blog-packs/incoming/ and click Add.

Links inside the articles point to your service pages, pricing, free audit, contact page and to
other articles. A link only appears once its page or article is live, so scheduled articles never
link to pages that do not exist yet. When a scheduled article goes live, the other imported
articles you have not edited are updated with the new link.

If your site has little traffic, WordPress sometimes misses a scheduled time ("Missed schedule").
The plugin checks every few minutes and publishes imported articles whose time has passed.

Articles you edit are never changed by the plugin. Importing the same pack twice skips articles
that are already on your site.

Works best with the Nabia theme (FAQ schema, related articles on service pages), and works with
any other theme too.

== Blog pack format ==

A .zip with:

* pack.json (optional): {"name": "...", "description": "..."}
* posts/*.html: one article per file. The file starts with a JSON comment with title, slug,
  excerpt, keyword, category, tags, service and faq, followed by the article HTML.
* covers/<slug>.jpg (or .png / .webp): cover image for the article with that slug.
* images/*.jpg: images used inside articles as <figure><img src="{{img:file.jpg}}" alt="..."><figcaption>...</figcaption></figure>
* Article details can include seo_title, meta_description and cover_alt.
* pack.json can suggest a schedule: {"schedule": {"every": 2, "unit": "days", "weekdays": [1,2,3,4,5,6,7], "time": "09:00"}}

== Changelog ==

= 1.1.0 =
* SEO title and meta description for Rank Math and Yoast (plus the page title without an SEO plugin).
* Images inside articles, uploaded with alt text and captions as image blocks.
* Cover image alt text.
* Packs can pre-fill the schedule.
* Add packs from the server when the zip is bigger than the upload limit.
* Clear message when a zip is too big to upload.
* Result table shows what was set on every article.
* Links to service pages also find pages with other common names.
* Link updates change only the content, never the status or date.

= 1.0.0 =
* First version.
