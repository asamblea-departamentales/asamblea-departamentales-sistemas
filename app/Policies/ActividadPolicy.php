<?php

namespace App\Policies;

use App\Models\Actividad;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActividadPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_actividad');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Actividad $actividad): bool
    {
        // Primero verificar permiso básico
        if (! $user->can('view_actividad')) {
            return false;
        }

        // GOL o Administrador → pueden ver todas
        if ($user->hasRole(['Administrador', 'GOL'])) {
            return true;
        }

        // Usuarios normales → solo su departamental
        return $actividad->departamental_id === $user->departamental_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // GOL NO puede crear
        if ($user->hasRole('GOL')) {
            return false;
        }

        return $user->can('create_actividad');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Actividad $actividad): bool
    {
        // GOL NO puede editar
        if ($user->hasRole('GOL')) {
            return false;
        }

        // Primero verificar permiso básico
        if (! $user->can('update_actividad')) {
            return false;
        }

        // Administrador sí puede editar todo
        if ($user->hasRole('Administrador')) {
            return true;
        }

        // Usuarios normales → solo su departamental
        return $actividad->departamental_id === $user->departamental_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Actividad $actividad): bool
    {
        // GOL NO puede eliminar
        if ($user->hasRole('GOL')) {
            return false;
        }

        // Primero verificar permiso básico
        if (! $user->can('delete_actividad')) {
            return false;
        }

        // Administrador sí puede eliminar todo
        if ($user->hasRole('Administrador')) {
            return true;
        }

        // Usuarios normales → solo su departamental
        return $actividad->departamental_id === $user->departamental_id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_actividad');
    }

    public function forceDelete(User $user, Actividad $actividad): bool
    {
        return $user->can('force_delete_actividad');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_actividad');
    }

    public function restore(User $user, Actividad $actividad): bool
    {
        return $user->can('restore_actividad');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_actividad');
    }

    public function replicate(User $user, Actividad $actividad): bool
    {
        return $user->can('replicate_actividad');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_actividad');
    }
}
