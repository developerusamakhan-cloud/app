<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use App\Services\GoogleClientFactory;
use Google\Service\Gmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MailboxController extends Controller
{
    /**
     * List connected mailboxes. Minimal for Phase 1 — just enough to see
     * that a connect worked. The real admin UI comes in Phase 2.
     */
    public function index()
    {
        return Mailbox::query()
            ->select('id', 'email', 'daily_limit', 'sent_today', 'status')
            ->get();
    }

    /**
     * Step 1 of OAuth: send the user to Google's consent screen.
     *
     * We store a random `state` value in the session and send it along with
     * the request. Google echoes it back on the callback, and we check it
     * matches — that's what stops a third party from tricking the callback
     * into linking an account the user never actually consented to.
     */
    public function connect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $client = GoogleClientFactory::make();
        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }

    /**
     * Step 2 of OAuth: Google redirects back here with a one-time `code`.
     * We exchange it for an access token + refresh token, then store the
     * refresh token (encrypted, see Mailbox::$casts) keyed by the mailbox's
     * email address.
     */
    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_oauth_state');

        abort_unless(
            $expectedState && hash_equals($expectedState, (string) $request->query('state')),
            403,
            'Invalid OAuth state.'
        );

        if ($request->query('error')) {
            abort(400, 'Google OAuth error: '.$request->query('error'));
        }

        $client = GoogleClientFactory::make();
        $token = $client->fetchAccessTokenWithAuthCode($request->query('code'));

        if (isset($token['error'])) {
            abort(400, 'Google OAuth token exchange failed: '.($token['error_description'] ?? $token['error']));
        }

        // A refresh_token is only returned when the user is prompted for
        // consent (which GoogleClientFactory forces via prompt=consent).
        // Without it we can't get new access tokens later, so there's no
        // point saving a mailbox that can't send.
        abort_unless(isset($token['refresh_token']), 500,
            'Google did not return a refresh token. Revoke this app\'s access at '.
            'https://myaccount.google.com/permissions and try connecting again.'
        );

        $client->setAccessToken($token);
        $gmail = new Gmail($client);
        $email = $gmail->users->getProfile('me')->emailAddress;

        $mailbox = Mailbox::updateOrCreate(
            ['email' => $email],
            [
                'refresh_token' => $token['refresh_token'],
                'daily_limit' => 30,
                'sent_today' => 0,
                'last_reset_date' => now()->toDateString(),
                'status' => 'active',
            ]
        );

        return redirect()->route('mailboxes.index')->with('status', "Connected {$mailbox->email}");
    }
}
