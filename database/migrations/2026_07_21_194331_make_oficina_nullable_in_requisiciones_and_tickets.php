<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->string('oficina')->nullable()->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('oficina')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('requisiciones', function (Blueprint $table) {
            $table->string('oficina')->nullable(false)->change();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('oficina')->nullable(false)->change();
        });
    }
};
