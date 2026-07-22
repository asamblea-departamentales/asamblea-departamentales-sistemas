<?php

namespace App\Policies;

use App\Models\Contrato;
use App\Models\User;

class ContratoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Contrato $contrato): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $contrato->departamental_id === $user->departamental_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Contrato $contrato): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $contrato->departamental_id === $user->departamental_id;
    }

    public function delete(User $user, Contrato $contrato): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $contrato->departamental_id === $user->departamental_id;
    }
}
