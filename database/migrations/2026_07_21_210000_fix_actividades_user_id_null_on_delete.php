<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK constraint si existe como constraint real
        $hasFk = DB::select("SELECT COUNT(*) as cnt FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
            AND TABLE_NAME = 'actividades'
            AND CONSTRAINT_NAME = 'actividades_user_id_foreign'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'");

        if ($hasFk[0]->cnt > 0) {
            Schema::table('actividades', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        // 2. Drop índice huérfano si existe (mismo nombre que la FK)
        $hasIndex = DB::select("SHOW INDEX FROM actividades WHERE Key_name = 'actividades_user_id_foreign'");
        if (! empty($hasIndex)) {
            DB::unprepared('ALTER TABLE actividades DROP INDEX actividades_user_id_foreign');
        }

        // 3. Limpiar registros huérfanos
        DB::table('actividades')
            ->whereNotIn('user_id', fn ($q) => $q->select('id')->from('users'))
            ->update(['user_id' => null]);

        // 4. Hacer nullable + agregar FK limpia
        Schema::table('actividades', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('actividades')->whereNull('user_id')->update(['user_id' => DB::raw('UUID()')]);

        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->uuid('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
