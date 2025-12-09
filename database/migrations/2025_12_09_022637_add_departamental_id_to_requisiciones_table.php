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
        Schema::table('requisiciones', function (Blueprint $table) {
            if (!Schema::hasColumn('requisiciones', 'departamental_id')) {
                $table->unsignedBigInteger('departamental_id')->nullable()->after('observaciones');

                $table->foreign('departamental_id')
                    ->references('id')
                    ->on('departamentales')
                    ->cascadeOnUpdate()
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->dropForeign(['departamental_id']);
            $table->dropColumn('departamental_id');
        });
    }
};
