<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SupportTicketService
{
    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateVisible(Request $request, User $user): LengthAwarePaginator
    {
        $perPage = max(1, (int) $request->input('per_page', 10));

        $query = SupportTicket::query()
            ->visibleTo($user)
            ->with(['user:id,name,email', 'assignee:id,name,email']);

        $sortColumn = $request->input('sort_column', 'created_at');
        $sortDirection = strtolower((string) $request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortable = ['created_at', 'ticket_number', 'subject', 'status', 'priority', 'type', 'updated_at'];

        if (in_array($sortColumn, $sortable, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderByDesc('created_at');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage)->withQueryString()->through(fn (SupportTicket $t) => $this->ticketSummary($t));
    }

    /**
     * @return array<string, mixed>
     */
    public function ticketSummary(SupportTicket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'type' => $ticket->type,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'user' => $ticket->user,
            'assignee' => $ticket->assignee,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function ticketDetail(SupportTicket $ticket, User $viewer, bool $includeInternalReplies): array
    {
        $ticket->load([
            'user:id,name,email',
            'assignee:id,name,email',
            'parcel:id,tracking_number',
        ]);

        $repliesQuery = $ticket->replies()
            ->with('user:id,name,email')->oldest();

        if (! $includeInternalReplies) {
            $repliesQuery->where('is_internal', false);
        }

        $replies = $repliesQuery->get()->map(fn (SupportTicketReply $r) => $this->replyPayload($r));

        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'type' => $ticket->type,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'parcel_id' => $ticket->parcel_id,
            'parcel' => $ticket->parcel ? [
                'id' => $ticket->parcel->id,
                'tracking_number' => $ticket->parcel->tracking_number,
            ] : null,
            'resolved_at' => $ticket->resolved_at?->toIso8601String(),
            'created_at' => $ticket->created_at?->toIso8601String(),
            'updated_at' => $ticket->updated_at?->toIso8601String(),
            'user' => $ticket->user,
            'assignee' => $ticket->assignee,
            'attachments' => $ticket->attachmentsPayload(),
            'replies' => $replies,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function replyPayload(SupportTicketReply $reply): array
    {
        return [
            'id' => $reply->id,
            'message' => $reply->message,
            'is_internal' => $reply->is_internal,
            'created_at' => $reply->created_at?->toIso8601String(),
            'user' => $reply->user,
            'attachments' => $reply->attachmentsPayload(),
        ];
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function attachFiles(SupportTicket|SupportTicketReply $model, array $files): void
    {
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $model->addMedia($file)->toMediaCollection('attachments');
            }
        }
    }

    public function verifyTicketMedia(SupportTicket $ticket, Media $media): bool
    {
        return $media->model_type === SupportTicket::class
            && (int) $media->model_id === $ticket->id;
    }

    public function verifyReplyMedia(SupportTicket $ticket, SupportTicketReply $reply, Media $media): bool
    {
        return $reply->ticket_id === $ticket->id
            && $media->model_type === SupportTicketReply::class
            && (int) $media->model_id === $reply->id;
    }
}
