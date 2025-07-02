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
        Schema::create('actividades_realizadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios_')->onDelete('cascade');
            $table->foreignId('actividad_programada_id')->constrained('actividades_programadas_')->onDelete('cascade');
            $table->date('fecha');
            $table->string('mes', 20);
            $table->string('programa');
            $table->text('macroactividad');
            $table->text('actividad');
            $table->time('hora');
            $table->integer('asistencia');
            $table->integer('hombres');
            $table->integer('mujeres');
            $table->string('departamental');
            $table->string('lugar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades_realizadas');
    }
};
