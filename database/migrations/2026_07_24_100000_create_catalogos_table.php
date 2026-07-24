<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->string('grupo'); // programa, rubro, tipo_insumo, tipo_contrato, tipo_ticket
            $table->string('slug');
            $table->string('label');
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['grupo', 'slug']);
            $table->index('grupo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};
