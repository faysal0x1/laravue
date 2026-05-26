<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SupportTicket extends Model implements HasMedia
{
    use InteractsWithMedia;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'parcel_id',
        'subject',
        'description',
        'type',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    #[\Override]
    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket): void {
            if ($ticket->ticket_number === null || $ticket->ticket_number === '') {
                $ticket->ticket_number = self::nextTicketNumber();
            }
        });
    }

    public static function nextTicketNumber(): string
    {
        do {
            $candidate = 'TKT-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (self::query()->where('ticket_number', $candidate)->exists());

        return $candidate;
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('attachments')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'text/plain',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    /**
     * @return array<int, array{id: int, name: string, file_name: string, mime_type: string|null, size: int, url: string}>
     */
    public function attachmentsPayload(): array
    {
        return $this->getMedia('attachments')->map(fn (Media $media) => [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'url' => $media->getUrl(),
        ])->values()->all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id');
    }

    protected function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('support-tickets.manage')) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user): void {
            $q->where('user_id', $user->id)
                ->orWhere('assigned_to', $user->id);
        });
    }
}
