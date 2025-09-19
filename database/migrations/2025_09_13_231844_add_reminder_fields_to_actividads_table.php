<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            // Columna para marcar cuando se envió el recordatorio (null = no enviado aún)
            $table->timestamp('reminder_notified_at')->nullable()->after('reminder_at');
        });
    }

    public function down(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropColumn('reminder_notified_at');
        });
    }
};
