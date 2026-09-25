=== Nabia Website Audit ===
Contributors: nabiakhan
Tags: website audit, seo audit, lead generation, pdf report
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Free website audit form that scores any website in real time and sends a branded PDF report.

== What it does ==

1. Visitors enter their website, name and email, and solve a small math captcha.
2. The plugin opens their homepage and runs about 40 checks in four groups:
   * Design & User Experience (mobile friendly, call to action, contact options, layout stability, fonts, social links)
   * SEO (title, meta description, H1, headings, alt text, canonical, indexing, language, Open Graph, schema, robots.txt, sitemap, internal links)
   * Content (amount of text, readability, sub headings, paragraph length, images, reviews and trust signals, text ratio, copyright year)
   * Speed & Security (HTTPS, server response, page weight, compression, number of files, render blocking scripts, WebP images, lazy loading, security headers, mixed content)
3. They see their overall score, grade, category scores and top 3 fixes right away, with a Download PDF button.
4. A branded PDF report (cover, action plan, a page per category with "how to fix" tips, and a closing page with your links) is emailed to them, and you get a copy.
5. Every request is saved under Website Audits in your dashboard with the scores, the full report and the PDF.

== Setup ==

1. Upload the zip in Plugins, Add New, Upload Plugin, and activate it.
2. Go to Website Audits, Settings. Check your name, colours, links (WhatsApp, Fiverr, Upwork, contact page, optional booking link) and email options.
3. Put the shortcode [nabia_website_audit] on any page. With the Nabia theme the homepage and Free Audit page use it automatically.
4. For reliable email delivery install an SMTP plugin such as WP Mail SMTP or FluentSMTP.

== Admin ==

* Website Audits lists every request with the overall score, the four category scores, the person and a Download PDF button. New requests are counted in the menu.
* Open an audit to see every check, download or open the PDF, view the online report, email the report again or run the audit again.

== Privacy and security ==

* Only public http and https websites can be audited (no local or private addresses).
* PDF reports are stored in wp-content/uploads/nabia-audits, which is blocked from direct access. They are only served through links with a secret key.
* Spam protection: nonce, hidden trap field, a minimum time on the form, the math captcha and a limit per visitor per hour.

== Credits ==

PDF creation uses FPDF (http://www.fpdf.org), a free PHP library. Its license is in lib/fpdf/license.txt.

== Changelog ==

= 1.0.0 =
* First release.
