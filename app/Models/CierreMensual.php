<?php

namespace App\Models;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CierreMensual extends Model
{
    use HasFactory, LogsActivity;

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
        'estado',
        'observaciones',
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
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });

        static::saved(function ($model) {
            Cache::forget("mes_cerrado_{$model->departamental_id}_{$model->mes}_{$model->año}");
        });

        static::deleted(function ($model) {
            Cache::forget("mes_cerrado_{$model->departamental_id}_{$model->mes}_{$model->año}");
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'mes', 'año', 'departamental_id', 'observaciones'])
            ->logOnlyDirty()
            ->dontLogIfAttributesChangedOnly(['pdf_path']);
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

    public static function mesCerrado($departamentalId, $mes, $año): bool
    {
        if ($departamentalId === null) {
            return false;
        }

        $cacheKey = "mes_cerrado_{$departamentalId}_{$mes}_{$año}";

        return Cache::remember($cacheKey, 60, function () use ($departamentalId, $mes, $año) {
            return self::where('departamental_id', $departamentalId)
                ->where('mes', $mes)
                ->where('año', $año)
                ->where('estado', 'aprobado')
                ->exists();
        });
    }

    public function getPorcentajeCumplimientoAttribute()
    {
        if ($this->actividades_proyectadas > 0) {
            return round(($this->actividades_ejecutadas / $this->actividades_proyectadas) * 100, 2);
        }

        return 0;
    }

    public function generarPDF()
    {
        $pdf = Pdf::loadView('pdf.cierre_mensual', [
            'cierre' => $this,
            'actividades' => $this->actividades,
            'meses' => app(\App\Services\CierreMensualService::class)->getMesesDisponibles(),
        ]);

        $pdfDir = storage_path('app/public/cierres');
        if (! is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $pdfPath = "cierres/cierre_{$this->id}.pdf";
        $pdf->save(storage_path("app/public/$pdfPath"));

        $this->update(['pdf_path' => $pdfPath]);

        return $pdfPath;
    }
}
