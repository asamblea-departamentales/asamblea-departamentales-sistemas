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
        Schema::create('atestados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actividad_realizada_id')
                ->constrained('actividades_realizadas') // Hace referencia a la tabla actividades_realizadas
                ->onDelete('cascade'); // Elimina el atestado si se elimina la actividad realizada
            $table->string('descripcion'); // Descripción del atestado
            $table->string('archivo'); // Ruta del archivo del atestado (ej: PDF, imagen, etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atestados');
    }
};
