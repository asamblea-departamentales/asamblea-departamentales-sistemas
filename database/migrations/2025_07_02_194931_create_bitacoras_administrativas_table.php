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
        Schema::create('bitacoras_administrativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('usuarios_') // Hace referencia a la tabla usuarios_
                ->onDelete('cascade'); // Hace referencia al usuario que realiza la bitácora

            // Puede estar ligada o no a una actividad programada
            $table->foreignId('actividad_id')
                ->constrained('actividades_programadas_') // Hace referencia a la tabla actividades_programadas_
                ->onDelete('cascade'); // Elimina la bitácora si se elimina la actividad programada

            $table->string('accion'); // Acción realizada en la bitácora
            $table->text('descripcion'); // Descripción de la acción realizada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras_administrativas');
    }
};
