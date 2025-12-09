<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si ya existe, eliminarla
        Schema::dropIfExists('media_manager_media');

        Schema::create('media_manager_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('name');
            $table->string('file'); // ruta relativa en el disk
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();
            $table->uuid('user_id')->nullable(); // UUID del usuario
            $table->timestamps();

            $table->foreign('folder_id')
                ->references('id')
                ->on('media_manager_folders')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_manager_media');
    }
};
