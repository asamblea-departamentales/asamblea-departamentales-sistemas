<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $ticket->departamental_id === $user->departamental_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $ticket->departamental_id === $user->departamental_id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $ticket->departamental_id === $user->departamental_id;
    }
}
