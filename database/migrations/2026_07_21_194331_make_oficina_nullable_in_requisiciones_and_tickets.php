<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('requisiciones', 'oficina')) {
            Schema::table('requisiciones', function (Blueprint $table) {
                $table->string('oficina')->nullable()->change();
            });
        }

        if (Schema::hasColumn('tickets', 'oficina')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('oficina')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('requisiciones', 'oficina')) {
            Schema::table('requisiciones', function (Blueprint $table) {
                $table->string('oficina')->nullable(false)->change();
            });
        }

        if (Schema::hasColumn('tickets', 'oficina')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('oficina')->nullable(false)->change();
            });
        }
    }
};
