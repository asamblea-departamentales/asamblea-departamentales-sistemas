<?php

namespace App\Policies;

use App\Models\Requisicion;
use App\Models\User;

class RequisicionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_requisicion');
    }

    public function view(User $user, Requisicion $requisicion): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $requisicion->departamental_id === $user->departamental_id;
    }

    public function create(User $user): bool
    {
        return $user->can('create_requisicion');
    }

    public function update(User $user, Requisicion $requisicion): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $requisicion->departamental_id === $user->departamental_id;
    }

    public function delete(User $user, Requisicion $requisicion): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $requisicion->departamental_id === $user->departamental_id;
    }
}
