<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cierres_mensuales', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relación con departamentales (BIGINT)
            $table->foreignId('departamental_id')
                  ->constrained('departamentales')
                  ->cascadeOnDelete();

            // Relación con users (UUID)
            $table->uuid('user_id');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();

            $table->integer('mes'); // 1-12
            $table->integer('año'); // Ej: 2025

            // Métricas consolidadas
            $table->integer('actividades_proyectadas')->default(0);
            $table->integer('actividades_ejecutadas')->default(0);
            $table->integer('actividades_pendientes')->default(0);
            $table->integer('actividades_canceladas')->default(0);
            $table->decimal('porcentaje_cumplimiento', 5, 2)->default(0);

            // Archivos
            $table->string('pdf_path')->nullable();

            // Estado
            $table->enum('estado', ['generado', 'aprobado', 'reabierto'])->default('generado');
            $table->text('observaciones')->nullable();

            $table->timestamp('fecha_cierre');
            $table->timestamps();

            // Evitar duplicados
            $table->unique(['departamental_id', 'mes', 'año']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cierres_mensuales');
    }
};
