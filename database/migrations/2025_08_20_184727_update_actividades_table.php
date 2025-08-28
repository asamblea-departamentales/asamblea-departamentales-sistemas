<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn(['asistentes']); // si todavía existe la vieja
    $table->integer('asistentes_hombres')->default(0)->after('lugar');
    $table->integer('asistentes_mujeres')->default(0)->after('asistentes_hombres');
    $table->integer('asistencia_completa')->default(0)->after('asistentes_mujeres');
});
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn([
                'lugar', 
                'asistentes_hombres', 
                'asistentes_mujeres', 
                'asistencia_completa'
            ]);
        });
    }
};
