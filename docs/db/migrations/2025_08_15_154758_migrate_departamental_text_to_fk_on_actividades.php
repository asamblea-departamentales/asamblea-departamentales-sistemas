<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Agrega la columna FK (nullable mientras hacemos backfill)
        Schema::table('actividades', function (Blueprint $table) {
            $table->unsignedBigInteger('departamental_id')->nullable()->after('fecha');
            // crea índice para rendimiento
            $table->index('departamental_id', 'actividades_departamental_id_index');
        });

        // 2) Backfill: intenta mapear por NOMBRE (o por CÓDIGO si lo prefieres)
        // ——— Mapea por nombre ———
        DB::statement("
            UPDATE actividades a
            JOIN departamentales d ON TRIM(LOWER(d.nombre)) = TRIM(LOWER(a.departamental))
            SET a.departamental_id = d.id
            WHERE a.departamental_id IS NULL
        ");

        // (Alternativa: mapear por 'codigo' si tu texto guarda códigos)
        // DB::statement("
        //     UPDATE actividades a
        //     JOIN departamentales d ON TRIM(LOWER(d.codigo)) = TRIM(LOWER(a.departamental))
        //     SET a.departamental_id = d.id
        //     WHERE a.departamental_id IS NULL
        // ");

        // 3) Para los que no se pudieron mapear, opcionalmente coloca una oficina por defecto
        //    (mejor corrige manualmente antes de bloquear con NOT NULL)
        // DB::statement("
        //     UPDATE actividades
        //     SET departamental_id = (SELECT id FROM departamentales ORDER BY id LIMIT 1)
        //     WHERE departamental_id IS NULL
        // ");

        // 4) Vuelve NOT NULL y agrega FK (hazlo solo si ya no quedan NULLs)
        //    Si aún hay NULLs, comenta esto, migra, corrige y luego crea otra migración para fijar NOT NULL + FK.
        $nulls = DB::table('actividades')->whereNull('departamental_id')->count();

        if ($nulls === 0) {
            Schema::table('actividades', function (Blueprint $table) {
                $table->unsignedBigInteger('departamental_id')->nullable(false)->change();
                $table->foreign('departamental_id', 'actividades_departamental_id_fk')
                    ->references('id')->on('departamentales')->cascadeOnDelete();
            });

            // 5) (Opcional y recomendado) eliminar la columna vieja de texto
            Schema::table('actividades', function (Blueprint $table) {
                if (Schema::hasColumn('actividades', 'departamental')) {
                    $table->dropColumn('departamental');
                }
            });
        }
    }

    public function down(): void
    {
        // Reversa minimal: quita FK/índice y columna nueva
        Schema::table('actividades', function (Blueprint $table) {
            if (Schema::hasColumn('actividades', 'departamental_id')) {
                if (Schema::hasColumn('actividades', 'departamental_id')) {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                    // Intenta soltar la FK si existe
                    try { $table->dropForeign('actividades_departamental_id_fk'); } catch (\Throwable $e) {}
                    try { $table->dropIndex('actividades_departamental_id_index'); } catch (\Throwable $e) {}
                }
                $table->dropColumn('departamental_id');
            }

            // Si eliminaste la columna vieja, podrías re-crear:
            // $table->string('departamental')->nullable();
        });
    }
};
