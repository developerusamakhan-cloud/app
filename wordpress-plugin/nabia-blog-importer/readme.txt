=== Nabia Blog Importer ===
Contributors: nabiakhan
Tags: import, blog, schedule, posts
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
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

Each article gets its cover image as the featured image, its category and tags, a FAQ section,
and the focus keyword and meta description for Yoast SEO or Rank Math.

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

== Changelog ==

= 1.0.0 =
* First version.
