<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            // Relación con cierres mensuales (UUID)
            $table->uuid('cierre_mensual_id')->nullable()->index();

            $table->foreign('cierre_mensual_id')
                  ->references('id')
                  ->on('cierres_mensuales')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['cierre_mensual_id']);
            $table->dropColumn('cierre_mensual_id');
        });
    }
};
