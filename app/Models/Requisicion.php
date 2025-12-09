<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Requisicion extends Model
{
    use HasFactory;

    protected $table = 'requisiciones';
    protected $keyType = 'string';
    public $incrementing = false; // Porque usamos UUID

    protected $fillable = [
        'tipo_insumo',
        'rubro',
        'cantidad',
        'fecha_solicitud',
        'estado_interno',
        'departamental_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'cantidad' => 'integer',
    ];

    // Estados comunes para requisiciones
    public const ESTADOS = [
        'SOLICITADA' => 'Solicitada',
        'EN_REVISION' => 'En Revisión',
        'APROBADA' => 'Aprobada',
        'RECHAZADA' => 'Rechazada',
        'COMPRADA' => 'Comprada',
        'ENTREGADA' => 'Entregada',
    ];

    // Tipos de insumo comunes
    public const TIPOS_INSUMO = [
        'OFICINA' => 'Materiales de Oficina',
        'LIMPIEZA' => 'Productos de Limpieza',
        'TECNOLOGIA' => 'Equipos de Tecnología',
        'MANTENIMIENTO' => 'Materiales de Mantenimiento',
        'SEGURIDAD' => 'Equipos de Seguridad',
        'OTROS' => 'Otros',
    ];

    // Rubros comunes
    public const RUBROS = [
        'PAPELERIA' => 'Papelería',
        'ELECTRONICA' => 'Electrónica',
        'MOBILIARIO' => 'Mobiliario',
        'CONSUMIBLES' => 'Consumibles',
        'HERRAMIENTAS' => 'Herramientas',
    ];

    protected static function boot(): void
    {
        parent::boot();
    
        static::creating(function ($model) {
            // Generar UUID para el id
            $model->id = (string) Str::uuid();
    
            // Asignar automáticamente el departamental del usuario logueado
            if (auth()->check()) {
                $model->departamental_id = auth()->user()->departamental_id;
            }
        });
    }
    

    // Scopes útiles
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado_interno', $estado);
    }

    public function scopePorTipoInsumo($query, $tipo)
    {
        return $query->where('tipo_insumo', $tipo);
    }

    public function scopePorRubro($query, $rubro)
    {
        return $query->where('rubro', $rubro);
    }

    public function scopePorDepartamental($query, $departamentalId)
{
    return $query->where('departamental_id', $departamentalId);
}


    public function scopePendientes($query)
    {
        return $query->whereIn('estado_interno', ['SOLICITADA', 'EN_REVISION', 'APROBADA']);
    }

    public function scopeCompletadas($query)
    {
        return $query->whereIn('estado_interno', ['COMPRADA', 'ENTREGADA']);
    }

    public function scopeRechazadas($query)
    {
        return $query->where('estado_interno', 'RECHAZADA');
    }

    // Métodos auxiliares
    public function estaPendiente()
    {
        return in_array($this->estado_interno, ['SOLICITADA', 'EN_REVISION', 'APROBADA']);
    }

    public function estaCompletada()
    {
        return in_array($this->estado_interno, ['COMPRADA', 'ENTREGADA']);
    }

    public function diasDesdeCreacion()
    {
        return $this->fecha_solicitud->diffInDays(now());
    }

    public function getUrgenciaAttribute()
    {
        $dias = $this->diasDesdeCreacion();
        
        if ($dias >= 15) {
            return 'Urgente';
        } elseif ($dias >= 7) {
            return 'Alta';
        } elseif ($dias >= 3) {
            return 'Media';
        }
        return 'Baja';
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estado_interno) {
            'SOLICITADA' => 'primary',
            'EN_REVISION' => 'warning',
            'APROBADA' => 'info',
            'RECHAZADA' => 'danger',
            'COMPRADA' => 'success',
            'ENTREGADA' => 'secondary',
            default => 'primary'
        };
    }

    public function getCantidadFormateadaAttribute()
    {
        return number_format($this->cantidad) . ' unidades';
    }

    // Método para obtener el siguiente estado posible
    public function getSiguientesEstadosPosibles()
    {
        return match($this->estado_interno) {
            'SOLICITADA' => ['EN_REVISION', 'RECHAZADA'],
            'EN_REVISION' => ['APROBADA', 'RECHAZADA'],
            'APROBADA' => ['COMPRADA', 'RECHAZADA'],
            'COMPRADA' => ['ENTREGADA'],
            default => []
        };
    }

    //Relacion con departamental
    public function departamental()
    {
        return $this->belongsTo(Departamental::class);
    }
}