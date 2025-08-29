<?php

namespace App\Models\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait BelongsToDepartamental
{
    /**
     * Relación con departamental
     */
    public function departamental()
    {
        return $this->belongsTo(\App\Models\Departamental::class);
    }

    /**
     * Scope para filtrar por el departamental del usuario actual
     */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $u = Filament::auth()->user() ?? auth()->user();
        if (! $u) {
            return $query;
        }

        $isCentral = $u->hasAnyRole(['Administrador', 'GOL'])
                     || $u->hasRole(config('filament-shield.super_admin.name'));
        $table = $query->getModel()->getTable();

        return ($isCentral || ! Schema::hasColumn($table, 'departamental_id'))
            ? $query
            : $query->where("{$table}.departamental_id", $u->departamental_id);
    }

    /**
     * Boot trait: asignar departamental_id y agregar global scope
     */
    protected static function bootBelongsToDepartamental(): void
    {
        static::creating(function ($model) {
            $u = Filament::auth()->user() ?? auth()->user();
            if (! $u) {
                return;
            }

            $isCentral = $u->hasAnyRole(['Administrador', 'GOL'])
                          || $u->hasRole(config('filament-shield.super_admin.name'));

            if (! $isCentral
                && empty($model->departamental_id)
                && Schema::hasColumn($model->getTable(), 'departamental_id')
            ) {
                $model->departamental_id = $u->departamental_id;
            }
        });

        static::addGlobalScope('departamental', function ($query) {
            $u = Filament::auth()->user() ?? auth()->user();
            if (! $u) {
                return;
            }

            $isCentral = $u->hasAnyRole(['Administrador', 'GOL'])
                          || $u->hasRole(config('filament-shield.super_admin.name'));
            $table = $query->getModel()->getTable();

            if (! $isCentral && Schema::hasColumn($table, 'departamental_id')) {
                $query->where("{$table}.departamental_id", $u->departamental_id);
            }
        });
    }
}
