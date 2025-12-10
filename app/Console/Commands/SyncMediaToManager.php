<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class SyncMediaToManager extends Command
{
    protected $signature = 'media:sync-to-manager';
    protected $description = 'Sincroniza archivos de Spatie al Media Manager';

    public function handle()
    {
        $this->info('Sincronizando archivos de Spatie al Media Manager...');

        $spatieMedia = SpatieMedia::all();
        $synced = 0;
        $skipped = 0;

        foreach ($spatieMedia as $media) {
            $folderName = ucfirst(str_replace('_', ' ', $media->collection_name));
            
            $folder = DB::table('folders')
                ->where('name', $folderName)
                ->first();

            if (!$folder) {
                $folderId = DB::table('folders')->insertGetId([
                    'name' => $folderName,
                    'description' => "Carpeta de {$folderName}",
                    'user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("📁 Carpeta creada: {$folderName} (ID: {$folderId})");
            } else {
                $folderId = $folder->id;
            }

            $exists = DB::table('media_has_models')
                ->where('media_id', $media->id)
                ->where('model_type', 'TomatoPHP\FilamentMediaManager\Models\Folder')
                ->where('model_id', $folderId)
                ->exists();

            if (!$exists) {
                DB::table('media_has_models')->insert([
                    'media_id' => $media->id,
                    'model_type' => 'TomatoPHP\FilamentMediaManager\Models\Folder',
                    'model_id' => $folderId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $synced++;
                $this->info("✓ Vinculado: {$media->file_name} → Carpeta: {$folderName}");
            } else {
                $skipped++;
                $this->comment("- Ya vinculado: {$media->file_name}");
            }
        }

        $this->newLine();
        $this->info("✅ Sincronización completada!");
        $this->info("   Archivos vinculados: {$synced}");
        $this->info("   Archivos omitidos: {$skipped}");
    }
}