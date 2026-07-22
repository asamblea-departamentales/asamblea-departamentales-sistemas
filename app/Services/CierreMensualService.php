<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\CierreMensual;
use App\Models\Departamental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CierreMensualService
{
    public function getMesesDisponibles(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    public function getNombreMes(int $mes): string
    {
        return $this->getMesesDisponibles()[$mes] ?? '';
    }

    /**
     * Genera cierre mensual individual o consolidado.
     *
     * @return array{generados: int, omitidos: int, cierres: array}
     */
    public function generarCierre(array $data, $user): array
    {
        $mes = $data['mes'];
        $año = $data['año'];
        $tipoCierre = $data['tipo_cierre'] ?? 'individual';

        $cierresGenerados = 0;
        $cierresOmitidos = 0;
        $cierresConsolidados = [];

        if ($tipoCierre === 'individual') {
            if (! $user->departamental_id) {
                return [
                    'generados' => 0,
                    'omitidos' => 1,
                    'cierres' => [],
                    'error' => 'El usuario no tiene departamental asignada.',
                ];
            }

            $resultado = $this->procesarCierreDepartamental(
                $user->departamental_id,
                $mes,
                $año,
                $user,
                $data['observaciones'] ?? null,
                true
            );

            $resultado ? $cierresGenerados++ : $cierresOmitidos++;
        }

        if ($tipoCierre === 'consolidado') {
            foreach (Departamental::all() as $departamental) {
                $resultado = $this->procesarCierreDepartamental(
                    $departamental->id,
                    $mes,
                    $año,
                    $user,
                    'Cierre consolidado generado automáticamente',
                    false
                );

                if ($resultado) {
                    $cierresGenerados++;
                    $cierre = CierreMensual::where('departamental_id', $departamental->id)
                        ->where('mes', $mes)
                        ->where('año', $año)
                        ->first();
                    $cierresConsolidados[] = $cierre;
                } else {
                    $cierresOmitidos++;
                }
            }

            if (! empty($cierresConsolidados)) {
                $this->generarPDFConsolidado($cierresConsolidados, $mes, $año);
            }
        }

        return [
            'generados' => $cierresGenerados,
            'omitidos' => $cierresOmitidos,
            'cierres' => $cierresConsolidados,
        ];
    }

    /**
     * Procesa el cierre de una departamental específica.
     */
    public function procesarCierreDepartamental(
        int $departamentalId,
        int $mes,
        int $año,
        $user,
        ?string $observaciones,
        bool $generarPDF = true
    ): bool {
        $cierreExistente = CierreMensual::where('departamental_id', $departamentalId)
            ->where('mes', $mes)
            ->where('año', $año)
            ->first();

        if ($cierreExistente && $cierreExistente->estado !== 'reabierto') {
            return false;
        }

        $fechaInicio = Carbon::createFromDate($año, $mes, 1)->startOfMonth();
        $fechaFin = $fechaInicio->copy()->endOfMonth();

        $actividades = Actividad::where('departamental_id', $departamentalId)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        $proyectadas = $actividades->count();
        $ejecutadas = $actividades->where('estado', 'Completada')->count();
        $pendientes = $actividades->where('estado', 'Pendiente')->count();
        $enProgreso = $actividades->where('estado', 'En Progreso')->count();
        $canceladas = $actividades->where('estado', 'Cancelada')->count();

        return DB::transaction(function () use (
            $departamentalId, $mes, $año, $user, $observaciones,
            $proyectadas, $ejecutadas, $pendientes, $enProgreso, $canceladas,
            $actividades, $generarPDF
        ) {
            $cierre = CierreMensual::updateOrCreate(
                [
                    'departamental_id' => $departamentalId,
                    'mes' => $mes,
                    'año' => $año,
                ],
                [
                    'user_id' => $user->id,
                    'actividades_proyectadas' => $proyectadas,
                    'actividades_ejecutadas' => $ejecutadas,
                    'actividades_pendientes' => $pendientes + $enProgreso,
                    'actividades_canceladas' => $canceladas,
                    'estado' => 'generado',
                    'observaciones' => $observaciones,
                    'fecha_cierre' => now(),
                ]
            );

            Actividad::whereIn('id', $actividades->pluck('id')->toArray())
                ->update(['cierre_mensual_id' => $cierre->id]);

            if ($generarPDF) {
                $cierre->generarPDF();
            }

            return true;
        });
    }

    public function generarPDFConsolidado($cierres, int $mes, int $año): void
    {
        $meses = $this->getMesesDisponibles();

        $pdf = Pdf::loadView('pdf.cierre_consolidado', [
            'cierres' => $cierres,
            'mes' => $mes,
            'año' => $año,
            'meses' => $meses,
        ]);

        $nombreArchivo = "informe_consolidado_{$año}_{$mes}.pdf";
        $rutaPDF = storage_path("app/public/cierres/{$nombreArchivo}");

        if (! file_exists(dirname($rutaPDF))) {
            mkdir(dirname($rutaPDF), 0755, true);
        }

        $pdf->save($rutaPDF);

        if (! empty($cierres)) {
            foreach ($cierres as $cierre) {
                $cierre->update(['pdf_path' => "cierres/{$nombreArchivo}"]);
            }
        }
    }
}
