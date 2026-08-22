<?php

namespace App\Services;

use App\Models\Mailbox;
use Google\Service\Gmail;
use Google\Service\Gmail\Message as GmailMessage;

class GmailMailer
{
    /**
     * Send an email through a mailbox's Gmail account and return the
     * Gmail message id + thread id, which is what later phases key
     * follow-ups and reply detection off of.
     *
     * @return array{message_id: string, thread_id: string}
     */
    public function send(Mailbox $mailbox, string $to, string $subject, string $body, ?string $threadId = null): array
    {
        $client = GoogleClientFactory::make();
        // Exchanging the stored refresh token for a fresh access token. Access
        // tokens expire after about an hour; refresh tokens don't (until the
        // user revokes access), so we do this exchange on every send rather
        // than trying to cache the access token.
        $client->fetchAccessTokenWithRefreshToken($mailbox->refresh_token);

        $gmail = new Gmail($client);

        $rawMessage = $this->buildRawMessage($mailbox->email, $to, $subject, $body);

        $message = new GmailMessage();
        $message->setRaw($rawMessage);

        if ($threadId) {
            // Setting threadId on send makes Gmail file this message into an
            // existing conversation instead of starting a new one — this is
            // what step 2/3 follow-ups use to land in the same thread as the
            // original outreach email.
            $message->setThreadId($threadId);
        }

        $sent = $gmail->users_messages->send('me', $message);

        return [
            'message_id' => $sent->getId(),
            'thread_id' => $sent->getThreadId(),
        ];
    }

    private function buildRawMessage(string $from, string $to, string $subject, string $body): string
    {
        $headers = implode("\r\n", [
            "From: {$from}",
            "To: {$to}",
            "Subject: {$subject}",
            'Content-Type: text/plain; charset=UTF-8',
        ]);

        $mime = "{$headers}\r\n\r\n{$body}";

        // Gmail's API wants the raw RFC 2822 message base64url-encoded
        // (base64, then + -> -, / -> _, trailing = stripped).
        return rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
    }
}
