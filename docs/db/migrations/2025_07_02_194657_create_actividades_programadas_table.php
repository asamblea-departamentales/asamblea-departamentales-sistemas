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
        Schema::create('actividades_programadas_', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('usuarios_')
                ->onDelete('cascade'); // Todo eso hace referencia al usuario que programa
            $table->date('fecha'); //Fecha de la actividad programada
            $table->string('dia', 20); //Día de la actividad programada
            $table->string('mes',20); //Mes de la actividad programada
            $table->string('programa'); //Programa de la actividad programada
            $table->text('macroactividad'); //Macroactividad de la actividad programada
            $table->text('actividad'); //Actividad de la actividad programada
            $table->time('hora'); //Hora de la actividad programada
            $table->string('departamental'); //Departamento de la actividad programada
            $table->string('lugar'); //Lugar de la actividad programada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades_programadas_');
    }
};