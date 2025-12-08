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
    Schema::table('tickets', function (Blueprint $table) {
        if (!Schema::hasColumn('tickets', 'departamental_id')) {
            $table->unsignedBigInteger('departamental_id')->nullable()->after('observaciones');

            $table->foreign('departamental_id')
                ->references('id')
                ->on('departamentales')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        }
    });
}

public function down(): void
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->dropForeign(['departamental_id']);
        $table->dropColumn('departamental_id');
    });
}

};
