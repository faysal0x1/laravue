<?php

declare(strict_types=1);

use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketMediaController;
use App\Http\Controllers\SupportTicketReplyController;
use Illuminate\Support\Facades\Route;

Route::post('support-tickets/table', [SupportTicketController::class, 'table'])->name('support-tickets.table');
Route::post('support-tickets/{support_ticket}/replies', [SupportTicketReplyController::class, 'store'])->name('support-tickets.replies.store');
Route::delete('support-tickets/{support_ticket}/attachments/{media}', [SupportTicketMediaController::class, 'destroyTicket'])->name('support-tickets.attachments.destroy');
Route::delete('support-tickets/{support_ticket}/replies/{reply}/attachments/{media}', [SupportTicketMediaController::class, 'destroyReply'])->name('support-tickets.replies.attachments.destroy');
Route::resource('support-tickets', SupportTicketController::class);
