<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            if (!Schema::hasColumn('actividades', 'asistentes_hombres')) {
                $table->integer('asistentes_hombres')->default(0)->after('lugar');
            }

            if (!Schema::hasColumn('actividades', 'asistentes_mujeres')) {
                $table->integer('asistentes_mujeres')->default(0)->after('asistentes_hombres');
            }

            if (!Schema::hasColumn('actividades', 'asistencia_completa')) {
                $table->integer('asistencia_completa')->default(0)->after('asistentes_mujeres');
            }

            // Opcional: eliminar la columna vieja si existe
            if (Schema::hasColumn('actividades', 'asistentes')) {
                $table->dropColumn('asistentes');
            }
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

            if (!Schema::hasColumn('actividades', 'asistentes')) {
                $table->integer('asistentes')->default(0)->after('lugar');
            }
        });
    }
};
