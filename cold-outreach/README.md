# Cold Outreach Tool

Self-hosted cold email tool. Sends and detects replies through the Gmail API
(no SMTP/IMAP) so follow-ups thread correctly and replies are caught reliably.

This README tracks setup + how to test each phase as it's built.

## Phase 1 — Send one email

What exists so far: a fresh Laravel install, a `mailboxes` table (just enough
to store one connected Gmail account's refresh token), an OAuth connect flow,
and a route that sends one hardcoded test email through a connected mailbox.

### 1. Google Cloud setup (do this yourself, in the Google Cloud Console)

1. Go to https://console.cloud.google.com/ and create a new project (or pick
   an existing one you're fine using for this).
2. **Enable the Gmail API**: APIs & Services → Library → search "Gmail API" →
   Enable.
3. **Configure the OAuth consent screen**: APIs & Services → OAuth consent
   screen.
   - User type: External (unless you have a Google Workspace org, in which
     case Internal is simpler and skips verification entirely).
   - Fill in the required app name/support email fields.
   - Scopes: add `https://www.googleapis.com/auth/gmail.send` and
     `https://www.googleapis.com/auth/gmail.readonly`.
   - Test users: add both Gmail addresses you'll be sending from. While the
     app is in "Testing" mode (the default, and fine for personal use —
     you never need to submit for verification), only test users can
     authorize it.
4. **Create OAuth credentials**: APIs & Services → Credentials → Create
   Credentials → OAuth client ID.
   - Application type: Web application.
   - Authorized redirect URI: `http://localhost:8000/mailboxes/callback`
     (match this to whatever `APP_URL` you run locally with).
   - Save. Copy the **Client ID** and **Client secret**.

### 2. Configure the app

```
cd cold-outreach
cp .env.example .env   # already done if you're continuing this session
php artisan key:generate
```

Edit `.env` and fill in:

```
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
```

`GOOGLE_REDIRECT_URI` is already set to `${APP_URL}/mailboxes/callback` —
leave it unless you change `APP_URL`.

Install dependencies and set up the database (SQLite, one file, zero config):

```
composer install
touch database/database.sqlite   # only if it doesn't already exist
php artisan migrate
```

### 3. Test it

```
php artisan serve
```

1. Visit `http://localhost:8000/mailboxes/connect` — you'll be sent to
   Google's consent screen. Approve it.
2. You'll land back on `http://localhost:8000/mailboxes`, which lists the
   connected mailbox as JSON (email, daily limit, sent count, status).
3. Grab the `id` from that JSON and visit
   `http://localhost:8000/test-send/{id}` — this sends one hardcoded test
   email **to that same mailbox** (so you don't need a second inbox to check
   it landed) via the Gmail API. The response is JSON with the Gmail
   `message_id` and `thread_id` it returned.
4. Check that mailbox's inbox — you should see the test email.

If you want to send to a different address instead of back to yourself, add
`?to=someone@example.com` to the test-send URL.

**Why a refresh token, and why `prompt=consent`**: Gmail API calls need a
short-lived access token, refreshed using a long-lived refresh token. Google
only ever hands back a refresh token when you're actively prompted for
consent — a second, silent authorization would return an access token only.
Forcing the consent prompt on every connect guarantees we get one to store.
If you ever see "Google did not return a refresh token," it means Google
still had a cached grant for that account — revoke access at
https://myaccount.google.com/permissions and reconnect.

**Why the refresh token is encrypted at rest**: `Mailbox::$casts` marks
`refresh_token` as `encrypted`, so Eloquent transparently encrypts it with
your app's `APP_KEY` before it touches SQLite and decrypts it back on read.
The database file itself never holds a usable secret.

---

Phases 2–5 (data layer/admin, scheduler, reply detection, stats/compliance)
are not built yet.
