<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->unsignedBigInteger('departamental_id')->nullable()->after('id');
            $table->foreign('departamental_id')->references('id')->on('departamentales')
                ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropForeign(['departamental_id']);
            $table->dropColumn('departamental_id');
        });
    }
};
