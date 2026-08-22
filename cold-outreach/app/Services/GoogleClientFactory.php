<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GoogleClientFactory
{
    /**
     * Build a Google API client configured for the Gmail scopes this app needs.
     *
     * `access_type=offline` + `prompt=consent` is what makes Google hand back
     * a refresh_token. Without both, a user who has already granted consent
     * once just gets an access token back on reconnect, and we'd have nothing
     * to store for future sends.
     */
    public static function make(): Client
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            Gmail::GMAIL_SEND,
            Gmail::GMAIL_READONLY,
        ]);

        return $client;
    }
}
