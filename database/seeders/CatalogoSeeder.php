<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // === PROGRAMAS ===
            'programa' => [
                ['slug' => 'programa_de_educacion_civica', 'label' => 'Programa de Educación Cívica', 'orden' => 1],
                ['slug' => 'programa_de_participacion_ciudadana', 'label' => 'Programa de Participación Ciudadana', 'orden' => 2],
                ['slug' => 'programa_de_atencion_ciudadana', 'label' => 'Programa de Atención Ciudadana', 'orden' => 3],
                ['slug' => 'otro', 'label' => 'Otro', 'orden' => 99],
            ],

            // === RUBROS ===
            'rubro' => [
                ['slug' => 'PAPELERIA', 'label' => 'Papelería', 'orden' => 1],
                ['slug' => 'ELECTRONICA', 'label' => 'Electrónica', 'orden' => 2],
                ['slug' => 'MOBILIARIO', 'label' => 'Mobiliario', 'orden' => 3],
                ['slug' => 'CONSUMIBLES', 'label' => 'Consumibles', 'orden' => 4],
                ['slug' => 'HERRAMIENTAS', 'label' => 'Herramientas', 'orden' => 5],
            ],

            // === TIPOS DE INSUMO ===
            'tipo_insumo' => [
                ['slug' => 'OFICINA', 'label' => 'Materiales de Oficina', 'orden' => 1],
                ['slug' => 'LIMPIEZA', 'label' => 'Productos de Limpieza', 'orden' => 2],
                ['slug' => 'TECNOLOGIA', 'label' => 'Equipos de Tecnología', 'orden' => 3],
                ['slug' => 'MANTENIMIENTO', 'label' => 'Materiales de Mantenimiento', 'orden' => 4],
                ['slug' => 'SEGURIDAD', 'label' => 'Equipos de Seguridad', 'orden' => 5],
                ['slug' => 'OTROS', 'label' => 'Otros', 'orden' => 6],
            ],

            // === TIPOS DE CONTRATO ===
            'tipo_contrato' => [
                ['slug' => 'SERVICIOS', 'label' => 'Servicios', 'orden' => 1],
                ['slug' => 'SUMINISTROS', 'label' => 'Suministros', 'orden' => 2],
                ['slug' => 'OBRAS', 'label' => 'Obras', 'orden' => 3],
                ['slug' => 'CONSULTORIA', 'label' => 'Consultoría', 'orden' => 4],
                ['slug' => 'MANTENIMIENTO', 'label' => 'Mantenimiento', 'orden' => 5],
                ['slug' => 'ARRENDAMIENTO', 'label' => 'Arrendamiento', 'orden' => 6],
            ],

            // === TIPOS DE TICKET ===
            'tipo_ticket' => [
                ['slug' => 'SOPORTE_TECNICO', 'label' => 'Soporte Técnico', 'orden' => 1],
                ['slug' => 'MANTENIMIENTO', 'label' => 'Mantenimiento', 'orden' => 2],
                ['slug' => 'SOLICITUD', 'label' => 'Solicitud', 'orden' => 3],
                ['slug' => 'INCIDENTE', 'label' => 'Incidente', 'orden' => 4],
                ['slug' => 'RECLAMO', 'label' => 'Reclamo', 'orden' => 5],
                ['slug' => 'CAMBIO_CONTRASENA', 'label' => 'Cambio de Contraseña', 'orden' => 6],
            ],
        ];

        foreach ($data as $grupo => $items) {
            foreach ($items as $item) {
                Catalogo::updateOrCreate(
                    ['grupo' => $grupo, 'slug' => $item['slug']],
                    [
                        'label' => $item['label'],
                        'orden' => $item['orden'],
                        'activo' => true,
                    ]
                );
            }
        }

        Catalogo::flushCache();
    }
}
