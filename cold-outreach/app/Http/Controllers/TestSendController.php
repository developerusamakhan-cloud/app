<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use App\Services\GmailMailer;
use Illuminate\Http\Request;

class TestSendController extends Controller
{
    /**
     * Phase 1 sanity check: send one hardcoded email through a connected
     * mailbox via the Gmail API and show the returned message/thread ids.
     * Defaults to sending to the mailbox's own address so you don't need a
     * second inbox to test with.
     */
    public function send(Request $request, Mailbox $mailbox, GmailMailer $mailer)
    {
        $to = $request->query('to', $mailbox->email);

        $result = $mailer->send(
            mailbox: $mailbox,
            to: $to,
            subject: 'Cold Outreach Tool — Phase 1 test',
            body: "This is a hardcoded test email sent via the Gmail API from {$mailbox->email}.\n\nIf you're reading this, OAuth + sending both work.",
        );

        return response()->json([
            'sent_from' => $mailbox->email,
            'sent_to' => $to,
            'gmail_message_id' => $result['message_id'],
            'gmail_thread_id' => $result['thread_id'],
        ]);
    }
}
