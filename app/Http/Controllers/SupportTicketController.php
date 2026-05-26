<?php

namespace App\Http\Controllers;

use App\Helpers\QueryBuilderHelper;
use App\Http\Requests\SupportTickets\StoreSupportTicketRequest;
use App\Http\Requests\SupportTickets\UpdateSupportTicketRequest;
use App\Models\Parcel;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function __construct(private readonly SupportTicketService $service) {}

    public function index(Request $request): Response
    {
        //  $this->authorize('viewAny', SupportTicket::class);

        return $this->renderIndex($request);
    }

    public function table(Request $request): Response
    {
        $this->authorize('viewAny', SupportTicket::class);

        return $this->renderIndex($request);
    }

    private function renderIndex(Request $request): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $tickets = $this->service->paginateVisible($request, $user);

        $filters = array_merge(
            QueryBuilderHelper::filters($request),
            $request->only(['type']),
        );

        return Inertia::render('SupportTickets/Index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'canManage' => $user->can('support-tickets.manage'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', SupportTicket::class);

        return Inertia::render('SupportTickets/Create', [
            'parcels' => $this->parcelOptions(),
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $data = $request->validated();
        unset($data['attachments']);

        $ticket = SupportTicket::query()->create(array_merge($data, [
            'user_id' => $user->id,
            'status' => 'open',
        ]));

        $this->service->attachFiles($ticket, $request->file('attachments', []));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ticket submitted successfully.',
        ]);
        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Ticket submitted successfully.',
        ]);

        return to_route('support-tickets.show', $ticket);
    }

    public function show(Request $request, SupportTicket $supportTicket): Response
    {
        $this->authorize('view', $supportTicket);

        $user = $request->user();
        if (! $user instanceof User) {
            abort(403);
        }

        $canManage = $user->can('support-tickets.manage');
        $ticketPayload = $this->service->ticketDetail($supportTicket, $user, $canManage);

        return Inertia::render('SupportTickets/Show', [
            'ticket' => $ticketPayload,
            'canManage' => $canManage,
            'canReply' => $user->can('reply', $supportTicket),
            'replyFormDefaults' => [
                'message' => '',
                'is_internal' => false,
            ],
        ]);
    }

    public function edit(SupportTicket $supportTicket): Response
    {
        $this->authorize('update', $supportTicket);

        $user = request()->user();
        if (! $user instanceof User) {
            abort(403);
        }

        return Inertia::render('SupportTickets/Edit', [
            'ticket' => [
                'id' => $supportTicket->id,
                'ticket_number' => $supportTicket->ticket_number,
                'subject' => $supportTicket->subject,
                'description' => $supportTicket->description,
                'type' => $supportTicket->type,
                'priority' => $supportTicket->priority,
                'status' => $supportTicket->status,
                'parcel_id' => $supportTicket->parcel_id,
                'assigned_to' => $supportTicket->assigned_to,
                'attachments' => $supportTicket->attachmentsPayload(),
            ],
            'parcels' => $this->parcelOptions(),
            'assignees' => $user->can('support-tickets.manage')
                ? User::query()->orderBy('name')->get(['id', 'name', 'email'])->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                ])->all()
                : [],
            'canManage' => $user->can('support-tickets.manage'),
        ]);
    }

    public function update(UpdateSupportTicketRequest $request, SupportTicket $supportTicket): RedirectResponse
    {
        $data = $request->validated();
        unset($data['attachments']);

        if (isset($data['status'])) {
            if (in_array($data['status'], ['resolved', 'closed'], true)) {
                $data['resolved_at'] = now();
            }
            if (in_array($data['status'], ['open', 'pending', 'in_progress'], true) && $supportTicket->resolved_at !== null) {
                $data['resolved_at'] = null;
            }
        }

        $supportTicket->update($data);

        $this->service->attachFiles($supportTicket, $request->file('attachments', []));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Ticket updated.',
        ]);
        session()->flash('toast', [
            'type' => 'success',
            'message' => 'Ticket updated.',
        ]);

        return to_route('support-tickets.show', $supportTicket);
    }

    public function destroy(SupportTicket $supportTicket): RedirectResponse
    {
        $this->authorize('delete', $supportTicket);

        $supportTicket->delete();

        return success_route('support-tickets.index', 'Ticket deleted.');
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function parcelOptions(): array
    {
        return Parcel::query()
            ->orderByDesc('id')
            ->limit(300)
            ->get(['id', 'tracking_number'])
            ->map(fn (Parcel $p) => [
                'id' => $p->id,
                'label' => $p->tracking_number ?? "#{$p->id}",
            ])
            ->all();
    }
}
