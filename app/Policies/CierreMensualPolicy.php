<?php

namespace App\Policies;

use App\Models\CierreMensual;
use App\Models\User;

class CierreMensualPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CierreMensual $cierre): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $cierre->departamental_id === $user->departamental_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['coordinador', 'ti', 'gol', 'super_admin']);
    }

    public function update(User $user, CierreMensual $cierre): bool
    {
        if ($user->isCentralRole()) {
            return true;
        }

        return $cierre->departamental_id === $user->departamental_id;
    }

    public function delete(User $user, CierreMensual $cierre): bool
    {
        return $user->isSuperAdmin();
    }
}
