=== Custom Schema Injector ===

A simple WordPress plugin that adds a "Custom Schema (JSON-LD)" textarea
to every post/page editor. Whatever JSON-LD is entered gets injected into
that specific page's <head> tag only — no global/site-wide schema.

== Installation ==

1. Copy the `custom-schema-injector` folder into `wp-content/plugins/`.
2. Activate "Custom Schema Injector" from the WordPress Plugins screen.
3. Edit any post/page — you'll see a "Custom Schema (JSON-LD)" box.
4. Paste valid JSON-LD (e.g. Article, Product, FAQPage schema) and update/publish.
5. View the page source — the schema appears inside <head> as:
   <script type="application/ld+json">...</script>

== Notes ==

- If the textarea is left empty, nothing is output on that page.
- If the JSON is invalid, it is still saved but an admin notice warns you,
  and nothing is rendered on the front end until it's fixed.
- Works on any public post type (posts, pages, custom post types).
