<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('support-tickets.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if (! $user->can('support-tickets.view')) {
            return false;
        }

        if ($user->can('support-tickets.manage')) {
            return true;
        }

        return $ticket->user_id === $user->id || $ticket->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('support-tickets.create');
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        if ($user->can('support-tickets.manage')) {
            return true;
        }

        if (! $user->can('support-tickets.update')) {
            return false;
        }

        return $ticket->user_id === $user->id && $ticket->status !== 'closed';
    }

    public function delete(User $user, SupportTicket $ticket): bool
    {
        if ($user->can('support-tickets.manage')) {
            return true;
        }

        if (! $user->can('support-tickets.delete')) {
            return false;
        }

        return $ticket->user_id === $user->id && in_array($ticket->status, ['open', 'pending', 'in_progress'], true);
    }

    public function reply(User $user, SupportTicket $ticket): bool
    {
        if (! $user->can('support-tickets.reply')) {
            return false;
        }

        return $this->view($user, $ticket) && $ticket->status !== 'closed';
    }
}
