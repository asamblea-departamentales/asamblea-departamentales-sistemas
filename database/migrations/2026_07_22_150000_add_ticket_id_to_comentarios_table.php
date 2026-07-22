<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            if (Schema::hasColumn('comentarios', 'ticket_id')) {
                return;
            }
            $table->dropForeign(['actividad_id']);
            $table->foreignId('actividad_id')->nullable()->change();
            $table->uuid('ticket_id')->nullable()->after('actividad_id');
            $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            if (Schema::hasColumn('comentarios', 'ticket_id')) {
                $table->dropForeign(['ticket_id']);
                $table->dropColumn('ticket_id');
                $table->foreign('actividad_id')->references('id')->on('actividades')->onDelete('cascade');
            }
        });
    }
};
