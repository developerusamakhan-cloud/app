<?php

use App\Http\Controllers\MailboxController;
use App\Http\Controllers\TestSendController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mailboxes', [MailboxController::class, 'index'])->name('mailboxes.index');
Route::get('/mailboxes/connect', [MailboxController::class, 'connect'])->name('mailboxes.connect');
Route::get('/mailboxes/callback', [MailboxController::class, 'callback'])->name('mailboxes.callback');

Route::get('/test-send/{mailbox}', [TestSendController::class, 'send'])->name('test-send');
