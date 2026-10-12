# Talkwyn QA report: Pro licensing and features

Date: 12 October 2026. Versions tested: Talkwyn 2.3.13, Talkwyn Pro 1.2.7, then 1.2.8, Talkwyn Hub 1.7.3.

## Test setup

Two separate WordPress sites, as in production:

| Role | WordPress | Address | Plugins |
|---|---|---|---|
| Hub (talkwyn.com) | 7.1.3 | hub.example.net:8899 | Talkwyn Hub 1.7.3 |
| Customer site | 6.4.13, the oldest version Pro supports | shop-a.example.com:8898 (and shop-b) | Talkwyn 2.3.13 and Talkwyn Pro |

Pro talked to the real Hub REST API, and every response was checked with the Hub's own Ed25519 key. The only test-only settings were:
- the Hub address and key
- allowing a local IP and port 8899 for downloads (WordPress blocks these by default, which does not affect talkwyn.com)
- a higher Hub rate limit for the regression run

Two code reviews (license client against the Hub API, and every Pro module) added findings. Each one was checked against the code before it was fixed.

## End to end results

| ID | Case | Result |
|---|---|---|
| TC-01 | Activate a Personal key, response signature verified | Pass |
| TC-02 | Same Personal key on a second site | Pass: refused with "already active on 1 site(s)" |
| TC-03 | Trial key: 15 days left, Agency features on | Pass |
| TC-04 | Activate an expired key on a new site | Pass: refused (renew first) |
| TC-05 | Agency key, unlimited sites | Pass: the admin shows "1 / Unlimited" |
| TC-06 | Deactivate from Pro frees the slot on the Hub | Pass |
| TC-07 | Paid license expires after activation | Pass: features stay, no update package, renew link shown |
| TC-08 | License revoked, then re-enabled on the Hub | Pass: Pro goes off, then on again |
| TC-09 | Hub unreachable for 0, 3 and 8 days | Pass: on, grace period, off. Back on when the Hub returns |
| TC-10 | Response signed with an untrusted key | Pass: rejected; Pro is kept as in an outage |
| TC-11 | Trial ends | Pass: off, "trial ended" and an upgrade link |
| TC-12 | New Pro release on the Hub, then a WordPress update on the customer site | Pass: 1.2.7 to 1.2.8 through the core updater, license kept, download counted |
| UI | All Pro admin tabs on WordPress 6.4, plus the front end widget | Pass: HTTP 200, no JavaScript errors |
| UN | Uninstall Pro | Pass in 1.2.8: Pro knowledge removed from the index, free plugin unaffected |

## Bugs found and fixed in Pro 1.2.8

Each fix has a regression test (R1 to R5), and all tests pass on 1.2.8.

| Severity | Bug | Fix |
|---|---|---|
| High | A paid license turned Pro off for up to a day when it reached its saved expiry date, even when it was already renewed on talkwyn.com (reproduced: `active=false`) | Only trials end at their local date. The next admin page checks with the Hub at once (R1) |
| Medium | Without WP-Cron, Pro switched off after 7 days with no warning (reproduced) | Overdue check from the admin (hourly lock) and after front end pages when 3 days overdue (R2) |
| Medium | A site freed from My Account got Pro back when the license expired | `was_site_active` flag. Older saved states keep their features (R3, R4) |
| Medium | A domain move with one failed reactivation left the site without an activation for good | Reactivation retried until the Hub answers clearly (R5) |
| Medium | Order lookup rate limit used the browser-chosen session id, so order numbers could be guessed | Limited per IP and per email, 5 an hour each |
| Medium | "I want to order 20 shirts" started an order status lookup | Only status words or a marked number (#1234, order no. 1234); stops waiting when the visitor moves on |
| Medium | Slack alerts passed visitor text unescaped (@channel, disguised links) | Escaped `&`, `<` and `>`; Telegram token validated |
| Medium | Streaming kept trying a failing provider first for 6 hours, with no overall time limit | Forgets it, 22 s budget like the free plugin, 25 s stream timeout |
| Medium | Uninstall left custom answers and PDF/URL sources in the free index with no way to remove them | Removed on uninstall; Pro keys removed with "Delete all data" |
| Low | Unsigned `success` flag decided success or failure | Signed `error_code` only |
| Low | Empty "View details" popup after a failed check; misleading "license does not allow this update" on network errors | Fixed |
| Low | The widget failed every message if the stream route was missing (cached page after Pro was turned off) | Falls back to the normal chat request |
| Low | Smart search build could loop forever and keep calling the paid embedding API | Stops when a batch makes no progress |
| Low | DOCX and PDF "zip bombs" could exhaust memory (admin only) | 20 MB limit |

## Open items (not changed, decide before scaling)

1. **Hub `.dev` domains count as development sites** (`Domain.php`, `*.dev`). `.dev` is a real public TLD, so a site like `brand.dev` uses no license slot. Suggest removing `*.dev` from the default patterns.
2. **Signing key rotation.** The Hub signs with the active key only. Before you click "Promote next key", ship a Pro release whose `hub-keys.php` lists both keys, and wait until most sites have updated. Otherwise older sites reject every response and switch Pro off after 7 days.
3. **Hub signer and invalid UTF-8.** `Signer.php` uses `json_encode` without flags. Invalid UTF-8 in a changelog would make every update check fail its signature check. Suggest `JSON_INVALID_UTF8_SUBSTITUTE` on both sides, or cleaning text on save.
4. **Rate limit per key** is 30 requests per 10 minutes, shared by all sites on that key. That is fine for daily checks, but an Agency customer who runs "check all updates" from a management tool on many sites could hit it. Consider a higher limit for Agency keys.
5. **Sitemap crawl runs inside the admin request**, up to about 20 fetches at 15 s each. Move it to cron and add `limit_response_size`.
6. **Uploaded knowledge files are public** in the media library and linked as sources. Mention this in the docs, or store them privately.
7. **Streaming ignores WordPress proxy settings** (raw cURL). On proxy-only hosts, streaming falls back to the slower normal reply.
8. **Settings import overwrites API keys and webhooks** without a preview. This is admin only, but only import files you created.
9. **The free plugin's minimum version for Pro is 2.1.0.** Check this against the APIs Pro calls before the next release.
