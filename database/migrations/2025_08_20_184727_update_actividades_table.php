<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            // Solo eliminar si la columna existe
            if (Schema::hasColumn('actividades', 'asistentes')) {
                $table->dropColumn('asistentes');
            }

            $table->integer('asistentes_hombres')->default(0)->after('lugar');
            $table->integer('asistentes_mujeres')->default(0)->after('asistentes_hombres');
            $table->integer('asistencia_completa')->default(0)->after('asistentes_mujeres');
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn([
                'asistentes_hombres',
                'asistentes_mujeres',
                'asistencia_completa',
            ]);

            // Solo agregar la columna vieja si no existe
            if (!Schema::hasColumn('actividades', 'asistentes')) {
                $table->integer('asistentes')->default(0)->after('lugar');
            }
        });
    }
};
