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
        Schema::create('usuarios_', function (Blueprint $table) {
            $table->id(); //ID del usuario
            $table->foreignId('rol_acceso_id') //Llave foránea al rol de acceso
                ->constrained('roles_acceso') // Hace referencia a la tabla roles_acceso
                ->onDelete('cascade'); // Elimina el usuario si se elimina el rol de acceso
            $table->string('nombre', 100); //Nombre del usuario
            $table->string('email', 100)->unique(); //Email del usuario, debe ser único
            $table->string('password'); //Contraseña del usuario hasheada
            $table->timestamps(); // Timestamps para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_');
    }
};
