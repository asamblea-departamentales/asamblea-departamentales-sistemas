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
        Schema::create('roles_acceso', function (Blueprint $table) {
            $table->id(); // Llave primaria
            $table->string('nombre', 100)->unique(); // Nombre del rol de acceso unico
            $table->string('descripcion', 255)->nullable(); // Descripción del rol de acceso
            $table->timestamps(); // Timestamps para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_acceso');
    }
};
