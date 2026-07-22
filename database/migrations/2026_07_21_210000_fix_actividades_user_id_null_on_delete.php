<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actividades', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::table('actividades')
            ->whereNotIn('user_id', fn ($q) => $q->select('id')->from('users'))
            ->update(['user_id' => null]);

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
