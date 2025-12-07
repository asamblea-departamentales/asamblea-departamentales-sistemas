<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
//NUEVO para relacion con Actividades
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreMensual extends Model
{
    protected $table = 'cierres_mensuales';

    // 👇 Ajustes para UUID
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
        'porcentaje_cumplimiento',
        'pdf_path',
        'estado',
        'observaciones',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_cierre' => 'datetime',
        'porcentaje_cumplimiento' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relaciones
    public function departamental(): BelongsTo
    {
        return $this->belongsTo(Departamental::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Determina si un mes está cerrado
    public static function mesCerrado(int $departamentalId, int $mes, int $año): bool
    {
        return self::where('departamental_id', $departamentalId)
            ->where('mes', $mes)
            ->where('año', $año)
            ->where('estado', '!=', 'reabierto')
            ->exists();
    }

    // Genera y guarda el PDF
    public function generarPDF(): void
    {
        $pdf = Pdf::loadView('pdf.cierre_mensual', [
            'cierre' => $this,
            'departamental' => $this->departamental,
            'usuario' => $this->user,
        ]);

        $path = "cierres/{$this->id}.pdf";

        Storage::disk('public')->put($path, $pdf->output());

        $this->pdf_path = $path;
        $this->save();
    }

    // Relación con actividades (NUEVO)
    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'cierre_mensual_id');
    }
}