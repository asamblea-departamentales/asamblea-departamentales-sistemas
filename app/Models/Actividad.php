<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDepartamental;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use TomatoPHP\FilamentMediaManager\Traits\InteractsWithMediaFolders;
use TomatoPHP\FilamentMediaManager\Models\Media as MediaManager;
/** Sincronización con Media Manager */
use TomatoPHP\FilamentMediaManager\Models\Media as ManagerMedia;
use TomatoPHP\FilamentMediaManager\Models\Folder;

class Actividad extends Model implements HasMedia
{
    use BelongsToDepartamental;
    use HasFactory;
    use InteractsWithMedia;
    use InteractsWithMediaFolders;

    protected $table = 'actividades';

    protected $fillable = [
        'user_id',
        'fecha',
        'departamental_id',
        'programa',
        'macroactividad',
        'estado',
        'star_date',
        'due_date',
        'reminder_at',
        'lugar',
        'asistentes_hombres',
        'asistentes_mujeres',
        'asistencia_completa',
    ];

    protected $casts = [
        'star_date' => 'datetime',
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'fecha' => 'date',
        'asistentes_hombres' => 'integer',
        'asistentes_mujeres' => 'integer',
        'asistencia_completa' => 'integer',
    ];

    protected $appends = ['asistencia_total'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->estado === 'Completada') {
                $validator = Validator::make($model->toArray(), [
                    'asistentes_hombres' => 'required|integer|min:0',
                    'asistentes_mujeres' => 'required|integer|min:0',
                    'asistencia_completa' => [
                        'required',
                        'integer',
                        'min:0',
                        function ($attribute, $value, $fail) use ($model) {
                            if ($value !== ($model->asistentes_hombres + $model->asistentes_mujeres)) {
                                $fail('La asistencia completa debe ser igual a la suma de asistentes hombres y mujeres.');
                            }
                        },
                    ],
                ]);

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }
            }
        });
    }

    protected static function booted()
    {
        static::created(function ($actividad) {
            $actividad->syncAtestadosToMediaManager();
        });
        
        static::updated(function ($actividad) {
            if ($actividad->getMedia('atestados')->isNotEmpty()) {
                $actividad->syncAtestadosToMediaManager();
            }
        });
    }

    /** Relaciones */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentarios::class);
    }

    public function cierreMensual()
    {
        return $this->belongsTo(CierreMensual::class);
    }

    /** Accessors */
    public function getAsistenciaTotalAttribute()
    {
        return $this->asistentes_hombres + $this->asistentes_mujeres;
    }

    /** Scopes */
    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query->where('due_date', '<', now())->where('estado', '!=', 'completada');
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }



    
    public function syncAtestadosToMediaManager(): void
    {
        if (!$this->departamental?->nombre) {
            return;
        }
    
        $carpetaName = "Atestados - {$this->departamental->nombre}";
    
        $folder = Folder::firstOrCreate(
            ['name' => $carpetaName],
            [
                'description' => "Carpeta privada de atestados – {$this->departamental->nombre}",
                'user_id' => $this->user_id,
                'is_public' => false,
            ]
        );
    
        foreach ($this->getMedia('atestados') as $media) {
    
            // Ruta relativa desde storage/app/public
            $relativePath = ltrim(str_replace(storage_path('app/public/'), '', $media->getPath()), '/');
    
            ManagerMedia::updateOrCreate(
                [
                    'file' => $relativePath,
                ],
                [
                    'name'       => $media->name ?? $media->file_name,
                    'mime_type'  => $media->mime_type,
                    'size'       => $media->size,
                    'folder_id'  => $folder->id,
                    'user_id'    => $this->user_id,
                ]
            );
        }
    }
    
    
}