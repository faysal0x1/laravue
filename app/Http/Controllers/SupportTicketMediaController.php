<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SupportTicketMediaController extends Controller
{
    public function __construct(private readonly SupportTicketService $service) {}

    public function destroyTicket(SupportTicket $supportTicket, Media $media): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        if (! $this->service->verifyTicketMedia($supportTicket, $media)) {
            abort(404);
        }

        $media->delete();

        return $this->flashAndBack('Attachment removed.');
    }

    public function destroyReply(SupportTicket $supportTicket, SupportTicketReply $reply, Media $media): RedirectResponse
    {
        $this->authorize('update', $supportTicket);

        if ($reply->ticket_id !== $supportTicket->id) {
            abort(404);
        }

        if (! $this->service->verifyReplyMedia($supportTicket, $reply, $media)) {
            abort(404);
        }

        $media->delete();

        return $this->flashAndBack('Attachment removed.');
    }

    private function flashAndBack(string $message): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
        session()->flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return back();
    }
}
