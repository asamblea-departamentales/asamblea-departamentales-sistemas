<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamental extends Model
{
    protected $table = 'departamentales';

    protected $fillable = ['nombre', 'codigo', 'activo'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    public function requisiciones()
    {
        return $this->hasMany(Requisicion::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function cierresMensuales()
    {
        return $this->hasMany(CierreMensual::class);
    }
}
