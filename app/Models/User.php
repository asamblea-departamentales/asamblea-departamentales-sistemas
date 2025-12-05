<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles, HasUuids;
    use Impersonate;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'firstname',
        'lastname',
        'password',
        'departamental_id',   // <-- AÑADIDO
        'activo',             // <-- AÑADIDO (opcional si lo usas)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /* ==============================
     |  Impersonate / Filament Auth
     ===============================*/
    public function canImpersonate()
    {
        return $this->isSuperAdmin();
    }

    public function canBeImpersonated()
    {
        // Puedes limitar según dominio si lo deseas.
        return true;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Reglas de acceso adicionales si manejas varios paneles.
        return true;
    }

    /* ==============================
     |  Nombre y Avatar en Filament
     ===============================*/
    public function getFilamentName(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getMedia('avatars')?->first()?->getUrl()
            ?? $this->getMedia('avatars')?->first()?->getUrl('thumb')
            ?? $this->avatar_url;
    }

    // Accessor "name"
    public function getNameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /* ==============================
     |  Roles / Helpers
     ===============================*/
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(config('filament-shield.super_admin.name'));
    }

    /** Roles “centrales” con vista global (ajusta nombres si usas otros) */
    public function isCentralRole(): bool
    {
        return $this->hasAnyRole(['Administrador', 'gol']) || $this->isSuperAdmin();
    }

    /* ==============================
     |  Relaciones
     ===============================*/
    public function departamental()
    {
        return $this->belongsTo(Departamental::class);
    }

    /* ==============================
     |  Scopes útiles
     ===============================*/
    public function scopeByDepartamental($query, ?int $departamentalId)
    {
        if (! $departamentalId) {
            return $query;
        } // permite NULL, incluye superadmins

        return $query->where('departamental_id', $departamentalId);
    }

    /* ==============================
     |  Media Library
     ===============================*/
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    public function getFallbackMediaUrl(string $collectionName = 'default', string $conversion = ''): string
    {
        if ($collectionName === 'avatars') {
            return 'https://ui-avatars.com/api/?name='.urlencode($this->name ?? $this->email ?? 'User');
        }

        return parent::getFallbackMediaUrl($collectionName, $conversion);
    }

    /* ==============================
 |  Notificaciones Broadcasted
 ===============================*/
/**
 * Los canales de notificación de transmisión del usuario.
 *
 * //@return array<string, mixed>|string
 */
 public function receivesBroadcastNotificationsOn(): string
 {
    return 'notifications.'.$this->id;
 }
}
