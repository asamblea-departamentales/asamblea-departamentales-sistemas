<?php

namespace App\Models;

use App\Models\Concerns\BelongsToDepartamental;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Actividad extends Model implements HasMedia
{
    use InteractsWithMedia;
    use BelongsToDepartamental;
    use HasFactory;

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

    /*
    |--------------------------------------------------------------------------
    | VALIDACIONES AUTOMÁTICAS
    |--------------------------------------------------------------------------
    */
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

    //Agregado 
    public function canViewMedia(): bool
{
    $user = auth()->user();

    return $user->departamental_id === $this->departamental_id
        || $user->hasRole('Administrador');
}

//NUEVO
public function getAtestadosUrlsAttribute()
{
    return $this->getMedia('atestados')->map(function ($media) {
        return [
            'url' => $media->getUrl(),
            'name' => $media->name,
            'size' => $media->size,
        ];
    })->toArray();
}

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getAsistenciaTotalAttribute()
    {
        return ($this->asistentes_hombres ?? 0) + ($this->asistentes_mujeres ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'Completada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'Pendiente');
    }

    public function scopeVencidas($query)
    {
        return $query
            ->where('due_date', '<', now())
            ->where('estado', '!=', 'Completada');
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | MEDIA (SPATIE)
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('atestados')
            ->useDisk('repositorio') // importante
            ->acceptsFile(function ($file) {
                return in_array($file->mimeType, [
                    'image/jpeg',
                    'image/png',
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]);
            });
    }
}