<?php

namespace App\Models\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait BelongsToDepartamental
{
    private static array $columnCache = [];

    /**
     * Relación con departamental
     */
    public function departamental()
    {
        return $this->belongsTo(\App\Models\Departamental::class);
    }

    private static function hasDepartamentalColumn(string $table): bool
    {
        if (! array_key_exists($table, self::$columnCache)) {
            self::$columnCache[$table] = Schema::hasColumn($table, 'departamental_id');
        }

        return self::$columnCache[$table];
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

        $isCentral = $u->hasAnyRole(['ti', 'gol'])
                     || $u->hasRole(config('filament-shield.super_admin.name'));
        $table = $query->getModel()->getTable();

        return ($isCentral || ! self::hasDepartamentalColumn($table))
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

            $isCentral = $u->hasAnyRole(['ti', 'gol'])
                          || $u->hasRole(config('filament-shield.super_admin.name'));

            if (! $isCentral
                && empty($model->departamental_id)
                && self::hasDepartamentalColumn($model->getTable())
            ) {
                $model->departamental_id = $u->departamental_id;
            }
        });

        static::addGlobalScope('departamental', function ($query) {
            $u = Filament::auth()->user() ?? auth()->user();
            if (! $u) {
                return;
            }

            $isCentral = $u->hasAnyRole(['ti', 'gol'])
                          || $u->hasRole(config('filament-shield.super_admin.name'));
            $table = $query->getModel()->getTable();

            if (! $isCentral && self::hasDepartamentalColumn($table)) {
                $query->where("{$table}.departamental_id", $u->departamental_id);
            }
        });
    }
}
