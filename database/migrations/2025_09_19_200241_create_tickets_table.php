<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID como PK
            $table->string('tipo_ticket');
            $table->string('motivo');
            $table->date('fecha_solicitud');
            $table->string('estado_interno');
            $table->string('oficina');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
