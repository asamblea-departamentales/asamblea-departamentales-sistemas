<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id'); // Cambié a UUID como en comentarios
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('fecha');
            $table->string('departamental');
            $table->string('programa');
            $table->string('macroactividad');
            $table->string('estado')->default('pendiente');
            $table->datetime('star_date');
            $table->datetime('due_date');
            $table->datetime('reminder_at')->nullable();
            $table->json('atestados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};