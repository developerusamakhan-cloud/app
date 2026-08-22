<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mailbox extends Model
{
    protected $fillable = [
        'email',
        'refresh_token',
        'daily_limit',
        'sent_today',
        'last_reset_date',
        'status',
    ];

    protected $casts = [
        // Laravel encrypts/decrypts this automatically using APP_KEY, so the
        // refresh token never sits in the database as plain text.
        'refresh_token' => 'encrypted',
        'last_reset_date' => 'date',
    ];
}
