=== Talkwyn AI Chatbot: Multilingual Answers and Lead Capture ===
Contributors: talkwyn
Tags: chatbot, ai chatbot, live chat, multilingual, lead generation
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.3.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI chatbot for WordPress that learns your site in one click, replies in each visitor's language with sources, and turns chats into leads.

== Description ==

Talkwyn is a free AI chatbot for WordPress and WooCommerce. It reads your pages, posts, products and Elementor content in one click, answers visitors in their own language, shows the sources behind its answers, and turns interested visitors into leads saved in your own database.

Connect a free AI key from Groq, Google Gemini, OpenRouter or Cloudflare Workers AI and your chatbot is live in about five minutes. Talkwyn charges no monthly fee and nothing per conversation, and it moves to the next provider automatically when one fails or hits its limit.

= What it does =

* **One-click site scan.** Published pages, posts and WooCommerce products (price, stock, attributes), Elementor content and your menus. Custom fields are off by default and use an allow list.
* **Always up to date.** Content is refreshed when you save it and removed as soon as it is unpublished, trashed, deleted or password protected.
* **Free AI providers with fallback.** Groq, OpenRouter, Google Gemini and Cloudflare Workers AI. Talkwyn remembers the provider that worked last and falls back to answers built from your pages if every provider is down.
* **Model lists from each provider.** Load the current models from the provider's API or type your own.
* **Replies in your visitor's language.** Detects the language of each message, follows switches, supports right-to-left scripts, and works with WPML and Polylang string translation.
* **Source links** under answers, and **safe formatting** (bold, lists, links) through an allow-list renderer.
* **Conversations that survive page changes.** History is kept on your server by session, so a visitor can browse while they chat.
* **Smart lead capture.** Talkwyn answers first, then asks whether the visitor wants a follow-up after a few real questions, or sooner when they ask about prices, a call or a quote. Required fields, a consent checkbox, a honeypot, a rate limit and optional Cloudflare Turnstile. Leads are emailed to you and can be exported as CSV.
* **Chat logs** with a retention setting (30 days by default), helpful or not helpful feedback, and copy buttons.
* **Your look.** Brand colour with Smart Contrast (readable text picked for you, with a warning for colours that are hard to read), avatar, launcher icon and text, position, size, full screen, full screen on phones, and rules for pages where the chat is hidden.
* **Privacy tools.** A notice under the chat, rate limiting by session and IP, personal data export and erase by email, and data removal on uninstall only when you choose it.
* **Chat menu.** Visitors can set their name, email themselves the transcript, pick a reply language, turn sound off, open the chat in its own window, or start a new chat.
* **Who sees it.** Show the chat to everyone, only logged in or only logged out visitors, and on all devices, desktop only or phones only.
* **Weekly summary** on the dashboard: chats and leads compared with the week before, and the top questions visitors asked.
* **Setup wizard.** Add a free AI key and test it, scan your site, pick a colour, go live.
* **Shortcode and block.** `[talkwyn_chat]` or the Talkwyn Chat block to place the chat inside a page.

= Talkwyn Pro =

Talkwyn works on its own. Talkwyn Pro is a separate add-on from talkwyn.com with a free 15-day trial. It adds paid AI providers (OpenAI, Anthropic Claude, Mistral, DeepSeek), smart search by meaning, PDF, DOCX and URL knowledge, custom answers, streaming replies, analytics, an unanswered questions inbox, WooCommerce product cards and order lookup, Slack and Telegram lead alerts, proactive messages, business hours, and white label on the Agency plan. If Pro stops, the free plugin keeps working with all your data.

== External services ==

Talkwyn sends data to an outside service only when you configure it.

**WordPress AI Client (WordPress 7.0 and later).** By default Talkwyn answers through the AI Client built into WordPress, using the provider and key you set up for the whole site in Settings > Connectors. That provider's own terms and privacy policy apply, and Talkwyn stores no key for it.

**Direct AI providers (optional, to answer visitors).** These are only used if you add your own key for them in Talkwyn, for example on WordPress versions before 7.0 or as a fallback. When a visitor sends a message, Talkwyn sends the message, the recent conversation, the relevant text from your website and your instructions to the AI providers you added a key for, in your fallback order. When you press "Load models", Talkwyn asks that provider for its model list with your key. When you press "Test connection", a short test message is sent.

* Groq: https://groq.com/ ([terms](https://groq.com/terms-of-use/), [privacy](https://groq.com/privacy-policy/))
* OpenRouter: https://openrouter.ai/ ([terms](https://openrouter.ai/terms), [privacy](https://openrouter.ai/privacy))
* Google Gemini API: https://ai.google.dev/ ([terms](https://ai.google.dev/gemini-api/terms), [privacy](https://policies.google.com/privacy))
* Cloudflare Workers AI: https://developers.cloudflare.com/workers-ai/ ([terms](https://www.cloudflare.com/website-terms/), [privacy](https://www.cloudflare.com/privacypolicy/))

**Cloudflare Turnstile (optional spam check).** If you add Turnstile keys, the lead form loads the Turnstile script from challenges.cloudflare.com and Talkwyn sends the visitor's check token and IP address to Cloudflare to verify it. [Privacy](https://www.cloudflare.com/turnstile-privacy-policy/)

**talkwyn.com (optional email updates).** The setup wizard has an unchecked box for product news and tips. Only if you tick it, Talkwyn sends your email address, site URL and site language to talkwyn.com once. [Privacy](https://talkwyn.com/privacy/)

Otherwise Talkwyn does not contact talkwyn.com. The "Start free trial" and "Upgrade to Pro" links in the admin, and the optional "Powered by Talkwyn" badge and "Add chat to your website" menu item in the chat (both off by default), are plain links that send nothing until someone clicks them.

== Installation ==

1. In WordPress go to Plugins, Add New, search for Talkwyn, then Install and Activate. Or upload the `talkwyn` folder to `/wp-content/plugins/`.
2. The setup wizard opens. On WordPress 7.0 and later, Talkwyn uses the AI connection from Settings > Connectors; click Test. You can also paste a free AI key from a provider (links are shown) and click Test.
3. Click "Scan my site".
4. Choose your colour, assistant name and welcome message.
5. Decide on the badge and the news email (both off by default).
6. Turn the chat on and click Finish.

To place the chat inside a page, use the `[talkwyn_chat]` shortcode or the Talkwyn Chat block.

== Frequently Asked Questions ==

= Is it free? =

Yes. The plugin is free. AI answers are paid to the provider you choose, either through Settings > Connectors on WordPress 7.0 and later or with a direct key in Talkwyn, and Groq, OpenRouter, Google Gemini and Cloudflare Workers AI have free tiers that suit many small sites. Check each provider's current limits.

= What happens when a provider hits its limit? =

Talkwyn tries the next provider in your fallback order. If none answers, it replies with the most relevant parts of your pages, or with your fallback message when nothing matches.

= Which languages does it answer in? =

It replies in whatever language the visitor writes in, including right-to-left scripts such as Arabic and Urdu. The chat interface is translated into 16 languages, and your own interface text can be translated per language with WPML or Polylang.

= Will it make up answers? =

It answers business questions only from your own content and shows the sources. When your site does not cover a question, it says so.

= Does it work outside WordPress? =

Not yet. A Shopify app and a one-line embed for any website are in development.

= Does it use my private content? =

No. Only published, public content of the types you choose is scanned. Password protected, draft, private and trashed content is never used, and is removed from the knowledge as soon as it changes.

= Does it work with page caching? =

Yes. The chat fetches a fresh session token from an uncached endpoint when it opens, so cached pages do not break it.

= My site is behind Cloudflare. Is the rate limit per visitor? =

Turn on "My site is behind Cloudflare or a proxy" under Talkwyn, Privacy. Talkwyn then reads the visitor IP from the proxy headers.

= Where is my data stored? =

In your WordPress database. Chats, leads and knowledge stay on your site. Messages are sent to your AI provider to generate answers.

== Screenshots ==

1. The chat on a website, with source links and a lead offer.
2. Lead form with consent, then the "Lead saved" confirmation.
3. The setup wizard.
4. AI providers with fallback order, model lists and connection tests.
5. Appearance settings with Smart Contrast.
6. Leads and conversations.

== Changelog ==

= 2.3.14 =
* Fixed: Leads sent very quickly (for example with browser autofill) are now saved.
* New: "Write a review" link in the Talkwyn screens and on the Plugins screen.
* Changed: The Talkwyn menu icon shows in colour.

= 2.3.13 =
* New: On WordPress 7.0 and later, Talkwyn can answer through the AI connection set up in Settings > Connectors.
* Improved: Security and code quality.

= 2.3.12 =
* Fixed: A chat placed inside a page on block themes did not answer.
* Fixed: An inline chat no longer stops the page from scrolling.

= 2.3.10 =
* Fixed: Full screen chat on phones now fills the whole screen.

= 2.3.8 =
* Changed: Clearer plugin name, description and FAQ.
* Fixed: Chat window layout on small screens.

= 2.3.6 =
* Changed: The chat uses the bundled Inter font, so it looks the same on every theme.

= 2.3.1 =
* Changed: Sources under an answer are folded behind a "Sources" button and limited to three.

= 2.3.0 =
* Changed: The chat keeps its own look on any theme or page builder.

= 2.2.0 =
* New: Pro features overview tab.
* Changed: Cleaner admin tabs and consistent button sizes.

= 2.1.3 =
* Fixed: Leads from the chat form were not saved.

= 2.1.0 =
* New: Redesigned admin with a dashboard checklist, weekly summary and live preview.
* New: Chat menu with email transcript, language picker, sound, pop out and new chat.
* New: Show the chat by device or for logged in visitors only.
* Changed: Requires PHP 8.0.

= 2.0.0 =
* First release of Talkwyn: setup wizard, chat block and shortcode, lead capture with spam protection, multilingual answers, privacy tools and support for cached pages.

== Upgrade Notice ==

= 2.3.14 =
Fixes leads that were not saved when the form was sent very quickly.
