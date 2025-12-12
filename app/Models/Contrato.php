<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $table = 'contratos';
    protected $keyType = 'string';
    public $incrementing = false; // Porque usamos UUID
    protected $fillable = [
        'tipo',
        'proveedor',
        'monto',
        'fecha_inicio',
        'fecha_vencimiento',
        'oficina',
        'observaciones',
    ];


    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_vencimiento' => 'date',
        'monto' => 'decimal:2',
    ];


    // Agregar estos métodos dentro de tu clase Contrato

// Verificar si el contrato está vigente
public function estaVigente()
{
    return $this->fecha_vencimiento >= now();
}

// Días para vencimiento 
public function diasParaVencimiento()
{
    return (int) now()->diffInDays($this->fecha_vencimiento, false);
}

// Obtener estado del contrato
public function getEstadoAttribute()
{
    if ($this->estaVigente()) {
        $dias = $this->diasParaVencimiento();
        if ($dias <= 30) {
            return 'Por vencer';
        }
        return 'Vigente';
    }
    return 'Vencido';
}

// Scope para contratos vigentes
public function scopeVigentes($query)
{
    return $query->where('fecha_vencimiento', '>=', now());
}

// Scope para contratos vencidos
public function scopeVencidos($query)
{
    return $query->where('fecha_vencimiento', '<', now());
}

    protected static function boot(): void
    {   
        parent::boot();

        static::creating(function ($model) {
            $model -> id = (string) Str::uuid();
        });

    }
}
