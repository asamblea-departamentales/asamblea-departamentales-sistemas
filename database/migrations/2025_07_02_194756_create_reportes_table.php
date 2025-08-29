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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')
                ->constrained('usuarios_')
                ->onDelete('cascade'); // Elimina el reporte si se elimina el usuario
            $table->enum('tipo', ['Anual', 'Mensual']); // Tipo de reporte
            $table->date('fecha_inicio'); // Fecha de inicio del reporte
            $table->date('fecha_fin'); // Fecha de fin del reporte
            $table->text('descripcion'); // Descripción del reporte (JSON o resumen)
            $table->enum('estado', ['Pendiente', 'Aprobado', 'Rechazado']) // Estado del reporte
                ->default('Pendiente'); // Por defecto, el estado es Pendiente
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
