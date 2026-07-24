<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Catalogo extends Model
{
    protected $table = 'catalogos';

    protected $fillable = [
        'grupo',
        'slug',
        'label',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopePorGrupo(Builder $query, string $grupo): Builder
    {
        return $query->where('grupo', $grupo);
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC HELPERS
    |--------------------------------------------------------------------------
    */

    public static function options(string $grupo): array
    {
        return static::porGrupo($grupo)
            ->activos()
            ->orderBy('orden')
            ->orderBy('label')
            ->pluck('label', 'slug')
            ->toArray();
    }

    public static function label(string $grupo, ?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        $all = static::options($grupo);

        return $all[$slug] ?? $slug;
    }

    public static function slugs(string $grupo): array
    {
        return static::porGrupo($grupo)
            ->activos()
            ->orderBy('orden')
            ->pluck('slug')
            ->toArray();
    }

    public static function flushCache(): void
    {
        $grupos = ['programa', 'rubro', 'tipo_insumo', 'tipo_contrato', 'tipo_ticket'];

        foreach ($grupos as $grupo) {
            Cache::forget("catalogo_options_{$grupo}");
            Cache::forget("catalogo_slugs_{$grupo}");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MODEL OBSERVERS
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
