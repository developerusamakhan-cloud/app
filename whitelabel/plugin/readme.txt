=== AI Site Assistant ===
Tags: chatbot, ai chatbot, live chat, lead generation, customer support
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI chatbot that learns your website in one click, answers visitors in their language, and captures leads.

== Description ==

AI Site Assistant adds a chat to your website that answers visitors from your own pages, posts and products. It reads your content in one click, replies in the visitor's language, shows the pages behind each answer, and saves interested visitors as leads in your own database.

Everything shown to the site owner can carry your own brand. Open the plugin, go to the Branding tab, and set:

* **Name.** Shown in the admin side menu, on the Plugins screen and in the plugin's screens.
* **Author and author website.** Shown on the Plugins screen and used for the optional "Powered by" line in the chat.
* **Description.** Shown on the Plugins screen.
* **Menu icon.** A Dashicons class (for example `dashicons-format-chat`) or an image URL.
* **Logo.** Shown at the top of the plugin's screens.
* **Hide the Branding tab.** Hides the tab from the client. It stays reachable at a direct address that is shown on the tab before you hide it.

= Features =

* **One click website scan.** Pages, posts, WooCommerce products and Elementor content. Updates itself when content changes.
* **Answers in the visitor's language.** With the source pages shown under each answer.
* **AI providers with fallback.** The WordPress AI Client (WordPress 7.0 and later), Groq, OpenRouter, Google Gemini and Cloudflare Workers AI. If one fails, the next one answers.
* **Lead capture.** Name, email, phone and message, with consent, spam checks and CSV export.
* **Conversation log.** Read what visitors ask.
* **Design.** Colour, position, launcher icon, avatar, welcome message and suggested questions, with a live preview.
* **Privacy tools.** Data export and erase, and automatic deletion of old conversations.
* **More AI providers.** OpenAI, Anthropic Claude, Mistral and DeepSeek, next to the free ones.
* **Smart search.** Finds answers by meaning, not only matching words.
* **Extra knowledge.** Add PDF and DOCX files, web pages and your own custom answers.
* **Streaming replies.** Answers appear word by word.
* **Analytics and unanswered questions.** See what visitors ask and answer the questions the chat could not.
* **WooCommerce.** Product cards with price and Add to cart, and order status lookup.
* **Lead alerts.** Slack and Telegram, next to email.
* **Proactive messages and business hours.** Greet visitors after a few seconds or on scroll, and show an away status outside your hours.
* **Settings export and import.** Move a setup from one site to another.
* **Shortcode and block.** `[aisa_chat]` or the AI Chat block to place the chat inside a page.

= Outside services =

The plugin sends data to an outside service only when you configure it.

**WordPress AI Client (WordPress 7.0 and later).** Uses the AI provider and key set up for the whole site in Settings > Connectors. That provider's terms and privacy policy apply.

**Direct AI providers (optional).** Only used if you add your own key. When a visitor sends a message, the message, the recent conversation, the relevant text from your website and your instructions are sent to the providers you added a key for.

* OpenAI, Anthropic, Mistral and DeepSeek: only if you add a key for them. Their own terms and privacy policies apply.
* Groq: [Terms](https://groq.com/terms-of-use/), [Privacy](https://groq.com/privacy-policy/)
* OpenRouter: [Terms](https://openrouter.ai/terms), [Privacy](https://openrouter.ai/privacy)
* Google Gemini: [Terms](https://ai.google.dev/gemini-api/terms), [Privacy](https://policies.google.com/privacy)
* Cloudflare Workers AI: [Terms](https://www.cloudflare.com/website-terms/), [Privacy](https://www.cloudflare.com/privacypolicy/)

**Cloudflare Turnstile (optional spam check).** If you add Turnstile keys, the lead form loads the Turnstile script and sends the visitor's check token and IP address to Cloudflare. [Privacy](https://www.cloudflare.com/turnstile-privacy-policy/)

**Slack and Telegram (optional lead alerts).** If you add a webhook or bot token, new leads are sent there.

The plugin does not check for updates online, needs no license, and contacts no other service.

== Installation ==

1. In WordPress go to Plugins, Add New, Upload Plugin, choose the zip file, then Install and Activate.
2. The setup wizard opens. Connect an AI provider and click Test.
3. Scan your website.
4. Turn the chat on.
5. Open the Branding tab to set your name, author, icon and logo.

== Frequently Asked Questions ==

= Where do I change the name in the side menu? =

In the plugin's Branding tab. Save, and the side menu, the Plugins screen and the plugin's screens show the new name.

= I hid the Branding tab. How do I open it again? =

Go to `wp-admin/admin.php?page=aisa&tab=branding`.

= Is the plugin free to run? =

AI answers are paid to the provider you choose. Groq, OpenRouter, Google Gemini and Cloudflare Workers AI have free tiers that suit many small sites.

= What happens if the AI provider is down? =

The next provider in the fallback order answers. If none answers, the chat replies with the most relevant parts of your pages.

== Changelog ==

= 1.0.0 =
* First release.
