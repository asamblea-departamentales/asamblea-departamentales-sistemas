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
use TomatoPHP\FilamentMediaManager\Models\Folder;           // ← CORRECTO: dentro de los uses
use TomatoPHP\FilamentMediaManager\Models\Media as MediaManager; // ← opcional, para evitar conflicto

class Actividad extends Model implements HasMedia
{
    use BelongsToDepartamental;
    use HasFactory;                 // <- ✨ habilita la relación y el scope
    use InteractsWithMedia;
    use InteractsWithMediaFolders;

    protected $table = 'actividades';

    /**
     * IMPORTANTE:
     * Usa 'departamental_id' como FK (int/uuid) hacia departamentales.
     */
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
      //  'atestados', lo mismo aqui
        'lugar', // campo agregado
        'asistentes_hombres', // campo agregado
        'asistentes_mujeres', // campo agregado
        'asistencia_completa', // campo agregado
    ];

    protected $casts = [
        'star_date' => 'datetime',
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'fecha' => 'date',
      //  'atestados' => 'array', porque ahora usamos media library directamente
        'asistentes_hombres' => 'integer', // campo agregado
        'asistentes_mujeres' => 'integer', // campo agregado
        'asistencia_completa' => 'integer', // campo agregado
    ];

    // metodo agregado
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

    /** Relaciones */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentarios::class);
    }

    /** Accessor para URLs completas de archivos adjuntos */
    //public function getArchivosUrlsAttribute(): array  // <- minúsculas: Urls
    //{
      //  if (empty($this->atestados)) {
           // return [];
       // }

        //return collect($this->atestados)->map(fn ($path) => asset('storage/'.ltrim($path, '/')))->all();
  //  }

    // Agregado tambien
    public function getAsistenciaTotalAttribute()
    {
        return $this->asistentes_hombres + $this->asistentes_mujeres;
    }

    protected $appends = ['asistencia_total']; // <- Agrega asistencia_total a los atributos accesibles

    /** Scopes de estado (nombres alineados al valor) */
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

    /**
     * Extra: puedes filtrar por el usuario actual y su departamental
     * usando el scope del trait:
     * Actividad::forCurrentUser()->get();
     */

     //NUEVO para relacion con el Cierre Mensual
     public function cierreMensual()
     {
         return $this->belongsTo(CierreMensual::class, 'cierre_mensual_id');
     }

     // Usa Spatie para manejar los archivos
    public function registerMediaCollections(): void
    {
     $this->addMediaCollection('atestados')
        ->useDisk('public')
        ->acceptsMimeTypes([
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'application/x-rar-compressed',
            'video/mp4', 'video/mpeg', 'video/quicktime',
            'audio/mpeg', 'audio/wav'
        ]);
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

public function syncAtestadosToMediaManager(): void
{
    if (!$this->departamental?->nombre) {
        return;
    }

    $departamentalNombre = $this->departamental->nombre;
    $folderName = "Atestados - {$departamentalNombre}";

    $folder = \TomatoPHP\FilamentMediaManager\Models\Folder::firstOrCreate(
        ['name' => $folderName],
        [
            'description' => "Carpeta privada de atestados – {$departamentalNombre}",
            'user_id' => $this->user_id,
            'is_public' => false,
        ]
    );

    foreach ($this->getMedia('atestados') as $media) {
        // Usar solo la ruta relativa sin "public/"
        $relativePath = ltrim(str_replace('public/', '', $media->getPathRelativeToRoot()), '/');

        \DB::table('media_has_models')->updateOrInsert(
            [
                'media_id' => $media->id,
                'model_type' => 'TomatoPHP\FilamentMediaManager\Models\Folder',
                'model_id' => $folder->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
}

