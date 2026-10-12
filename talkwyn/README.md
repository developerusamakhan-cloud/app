# Talkwyn

Source of the Talkwyn WordPress products. Each folder is the plugin or theme folder exactly as it goes inside its ZIP.

| Folder | Product | Version | Where it runs |
|---|---|---|---|
| `plugin/` | Talkwyn (free plugin, slug `talkwyn`) | 2.3.13 | WordPress.org and customer sites |
| `pro/` | Talkwyn Pro (add-on) | 1.2.8 | Customer sites with a license |
| `hub/` | Talkwyn Hub (licenses, releases, trials, partners) | 1.7.3 | talkwyn.com |
| `theme/` | Talkwyn theme (block theme for talkwyn.com) | 2.17.4 | talkwyn.com |

## Build a ZIP

Zip the folder under the name WordPress expects:

```
cd talkwyn && cp -r plugin /tmp/talkwyn && (cd /tmp && zip -r talkwyn-2.3.13.zip talkwyn)
cd talkwyn && cp -r hub /tmp/talkwyn-hub && (cd /tmp && zip -r talkwyn-hub-1.7.3.zip talkwyn-hub)
cd talkwyn && cp -r pro /tmp/talkwyn-pro && (cd /tmp && zip -r talkwyn-pro-1.2.8.zip talkwyn-pro)
cd talkwyn && cp -r theme /tmp/talkwyn && (cd /tmp && zip -r talkwyn-theme-2.17.4.zip talkwyn)
```

## Recent changes

- **Plugin 2.3.13:** WordPress.org review fixes. Escaped output, the pop-out styles are enqueued, and the WordPress AI Client (WordPress 7.0+) is the default AI provider.
- **Pro 1.2.8:** QA fixes for licensing (renewal, cron, freed sites, domain moves) and features (order lookup, Slack alerts, streaming, uninstall). See `QA-REPORT.md`.
- **Hub 1.7.3:**
  - The Releases screen shows the live version of each product and the full release history, with status, downloads, changelog, admin download and Make live (rollback).
  - The website download of the free plugin works without WooCommerce, and its problems are reported in the admin.
- **Theme 2.17.4:** The Download the ZIP button always uses the live release from Talkwyn Hub when it can serve one, and ignores a Site Settings link that points at a page of this site. The download box shows a notice when the ZIP cannot be served (2.17.3).

Each folder has its own README or CHANGELOG with details.
