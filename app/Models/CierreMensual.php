<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CierreMensual extends Model
{
    use HasFactory;

    protected $table = 'cierres_mensuales';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'departamental_id',
        'user_id',
        'mes',
        'año',
        'actividades_proyectadas',
        'actividades_ejecutadas',
        'actividades_pendientes',
        'actividades_canceladas',
        'fecha_cierre',
        'pdf_path',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
    ];

    // 👇 Genera UUID automáticamente
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class, 'cierre_mensual_id');
    }

    public function departamental()
    {
        return $this->belongsTo(Departamental::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function mesCerrado($departamentalId, $mes, $año)
    {
        return self::where('departamental_id', $departamentalId)
            ->where('mes', $mes)
            ->where('año', $año)
            ->exists();
        }

        public function getPorcentajeCumplimientoAttribute()
        {
            if ($this->actividades_proyectadas > 0) {
                return round(($this->actividades_ejecutadas / $this->actividades_proyectadas) * 100, 2);
            }
            return 0;
        }
            
}
