<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportTickets\StoreSupportTicketReplyRequest;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class SupportTicketReplyController extends Controller
{
    public function __construct(private readonly SupportTicketService $service) {}

    public function store(StoreSupportTicketReplyRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $validated = $request->validated();
        unset($validated['attachments']);

        $reply = SupportTicketReply::query()->create([
            'ticket_id' => $supportTicket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_internal' => $user->can('support-tickets.manage') && ($validated['is_internal'] ?? false),
        ]);

        $this->service->attachFiles($reply, $request->file('attachments', []));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Reply posted.',
        ]);
        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Reply posted.',
        ]);

        return to_route('support-tickets.show', $supportTicket);
    }
}
