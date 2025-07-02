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
        Schema::create('reporte_actividad', function (Blueprint $table) {
            $table->foreignId('reporte_id')->constrained('reportes')->onDelete('cascade'); // Hace referencia a la tabla reportes
            $table->foreignId('actividad_id')->constrained('actividades_realizadas')->onDelete('cascade'); // Hace referencia a la tabla actividades_realizadas

            //Clave primaria compuesta
            $table->primary(['reporte_id', 'actividad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_actividad');
    }
};
