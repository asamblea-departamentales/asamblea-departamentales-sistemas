<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
