# Talkwyn Pro changelog

## 1.2.8

QA fixes from an end to end test of the license flow and a review of every Pro module.

License:
- A paid license no longer switches Pro off for up to a day when it reaches its saved expiry date. The license may already be renewed on talkwyn.com, so the site checks at once (next admin page) and keeps Pro on. Only a trial ends at its date.
- WP-Cron that never runs no longer turns Pro off silently after 7 days. An overdue check runs from the admin (at most hourly) and, three days overdue, after a front end page is sent (at most every 6 hours).
- A site freed from My Account does not get Pro back when the license later expires (`was_site_active`). Saved states from older versions keep their features.
- After a site move, reactivation is retried on every check until talkwyn.com gives a clear answer, instead of leaving the site without an activation after one failed try.
- Success or failure and the error code are read only from the signed data.
- "View details" no longer opens an empty popup after a failed update check, and a failed download says the license server could not be reached instead of "Your license does not allow this update".

Features:
- Order lookup: tries are limited per visitor address and per email (5 an hour each). The session id was part of the key and could be changed to guess order numbers.
- Order lookup starts only for status questions or a marked order number (#1234, order no. 1234). "I want to order 20 shirts" no longer asks for an order number, and the bot stops waiting for order details when the visitor talks about something else.
- Slack alerts escape what visitors typed, so a lead cannot ping @channel or add a disguised link. The Telegram token is checked and used as is.
- Streaming: a provider that fails is no longer tried first for 6 hours, the reply has the same 22 second budget as the free plugin, and each stream times out after 25 seconds (was 45).
- The widget falls back to the normal chat request when the stream route is missing (for example a cached page after Pro was turned off).
- Smart search: building vectors stops when a batch makes no progress, instead of calling the embedding API again and again.
- DOCX and PDF reading refuses content that unpacks to more than 20 MB.
- Uninstall removes custom answers and PDF, document and web page sources from the knowledge index, and, with "Delete all data" on, the Pro API keys and alert settings. Tables are dropped with `%i`.

## 1.2.7

- Proactive messages: the settings sanitizer keeps at most 5 rules, matching the 5 rows in the admin (it accepted 10 before).
- `uninstall.php`: the header comment claimed the license is released on uninstall; it is not. It now says to deactivate the license first (Talkwyn, License) or deactivate the site later from My Account on talkwyn.com.

## 1.2.6

- White label (admin logo, menu name, chat menu link) and settings export and import load only for an Agency license, a license whose features include `white_label`, or a running trial. New `License::agency()` and the `talkwyn_pro_agency` filter. An expired paid Agency license keeps them, like every other Pro feature.
- Other plans see a short card under Pro settings that points to the Agency plan. Saved white label settings are kept.

## 1.2.5

- Product card titles use weight 600 (was 700), matching the Inter type in Talkwyn 2.3.6.

## 1.2.4

Fixes from the WordPress Plugin Check report on 1.2.3:
- License client strings use the `talkwyn-pro` text domain (was `talkwyn`). The copy in `client-sdk/` is updated to match.
- Database queries in Insights, Search and Knowledge pass table names through `$wpdb->prepare()` with `%i`.
- Errors thrown by file extraction and streaming are escaped, then decoded again before they show in the admin.
- The `detail` value in admin notices is sanitized straight from `$_GET` (no extra URL decode).
- AI provider addresses (OpenAI, Anthropic, Mistral, DeepSeek, Groq, OpenRouter, Gemini) carry a phpcs note: the site owner picks the provider and adds their own key.
- readme: Tested up to 7.1; Groq and OpenRouter added to External services.
- `HOOKS-USED.md` stays in the source but is left out of the release ZIP.

Not changed on purpose:
- The update checker and the `Update URI` header. Talkwyn Pro is sold and updated from talkwyn.com, not WordPress.org, so these stay. Plugin Check reports them because it assumes every plugin is hosted on WordPress.org.
- "Requires Plugins: talkwyn" warning. It goes away once the free Talkwyn plugin is live on WordPress.org.

## 1.2.3

- Pro widget styles (product cards and more) use the same ID-level weight as Talkwyn 2.3.0, so themes and page builders cannot restyle them.

## 1.2.2

- Includes the active talkwyn.com Hub public key in `includes/hub-keys.php`. License responses and updates are verified without any wp-config setting.

## 1.2.1

- License tab: key field and Activate button are 44px, matching the Talkwyn admin buttons.

## 1.2.0

- License: a paid license that expires keeps Pro features on. Only updates and support stop until renewal. A trial that ended, or a key that is revoked or suspended, turns Pro features off. The 7-day grace period still covers times when talkwyn.com cannot be reached.
- Admin: without a license only the License tab is added, with a "Start a free 15-day trial" link. No locked feature tabs are shown.
- Admin: buttons and cards use the new Talkwyn admin style.
- White label: admin logo URL, and a chat menu link (text and URL) that replaces "Add chat to your website".
- Pro settings: "Coming soon" card for live human takeover, booking integrations, WhatsApp channel, multiple bots, lead scoring and AI conversation summaries.
- Uses the renamed free plugin hooks (see HOOKS-USED.md). Needs Talkwyn 2.1.0 or newer and shows a notice otherwise.
- License client: the expired message now says Pro features keep working.

## 1.0.0

- First release.
