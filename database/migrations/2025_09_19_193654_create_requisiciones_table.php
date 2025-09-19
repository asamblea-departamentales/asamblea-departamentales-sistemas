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
    Schema::create('requisiciones', function (Blueprint $table) {
        $table->uuid('id')->primary(); // Usamos UUID como en los otros modelos
        $table->string('tipo_insumo');
        $table->string('rubro');
        $table->integer('cantidad');
        $table->date('fecha_solicitud');
        $table->string('estado_interno');
        $table->string('oficina');
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisiciones');
    }
};
