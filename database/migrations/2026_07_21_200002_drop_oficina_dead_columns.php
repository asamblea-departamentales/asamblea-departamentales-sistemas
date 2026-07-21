<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            if (Schema::hasColumn('requisiciones', 'oficina')) {
                $table->dropColumn('oficina');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'oficina')) {
                $table->dropColumn('oficina');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->string('oficina')->nullable()->after('estado_interno');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('oficina')->nullable()->after('estado_interno');
        });
    }
};
