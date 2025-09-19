<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $table = 'tickets';
    protected $keyType = 'string';
    public $incrementing = false; // Porque usamos UUID

    protected $fillable = [
        'tipo_ticket',
        'motivo',
        'fecha_solicitud',
        'estado_interno',
        'oficina',
        'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
    ];

    // Estados comunes para tickets
    public const ESTADOS = [
        'PENDIENTE' => 'Pendiente',
        'EN_PROCESO' => 'En Proceso',
        'RESUELTO' => 'Resuelto',
        'CERRADO' => 'Cerrado',
        'CANCELADO' => 'Cancelado',
    ];

    // Tipos de ticket comunes
    public const TIPOS = [
        'SOPORTE_TECNICO' => 'Soporte Técnico',
        'MANTENIMIENTO' => 'Mantenimiento',
        'SOLICITUD' => 'Solicitud',
        'INCIDENTE' => 'Incidente',
        'RECLAMO' => 'Reclamo',
    ];

    protected static function boot(): void
    {   
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Scopes útiles
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado_interno', $estado);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_ticket', $tipo);
    }

    public function scopePorOficina($query, $oficina)
    {
        return $query->where('oficina', $oficina);
    }

    public function scopeAbiertos($query)
    {
        return $query->whereNotIn('estado_interno', ['RESUELTO', 'CERRADO', 'CANCELADO']);
    }

    public function scopeCerrados($query)
    {
        return $query->whereIn('estado_interno', ['RESUELTO', 'CERRADO', 'CANCELADO']);
    }

    // Métodos auxiliares
    public function estaAbierto()
    {
        return !in_array($this->estado_interno, ['RESUELTO', 'CERRADO', 'CANCELADO']);
    }

    public function diasDesdeCreacion()
    {
        return $this->fecha_solicitud->diffInDays(now());
    }

    public function getPrioridadAttribute()
    {
        $dias = $this->diasDesdeCreacion();
        
        if ($dias >= 7) {
            return 'Alta';
        } elseif ($dias >= 3) {
            return 'Media';
        }
        return 'Baja';
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado_interno) {
            'PENDIENTE' => 'warning',
            'EN_PROCESO' => 'info',
            'RESUELTO' => 'success',
            'CERRADO' => 'secondary',
            'CANCELADO' => 'danger',
            default => 'primary'
        };
    }
}