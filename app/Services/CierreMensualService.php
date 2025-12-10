<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\CierreMensual;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class CierreMensualService
{
    public function generarCierre($departamentalId, $mes, $año, $userId)
    {
        // 1. Buscar actividades del mes
        $actividades = Actividad::whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->where('departamental_id', $departamentalId)
            ->get();

        // 2. Calcular totales
        $proyectadas = $actividades->count();
        $ejecutadas = $actividades->where('estado', 'Ejecutada')->count();
        $pendientes = $actividades->where('estado', 'Pendiente')->count();
        $canceladas = $actividades->where('estado', 'Cancelada')->count();

        // 3. Crear Cierre
        $cierre = CierreMensual::create([
            'departamental_id' => $departamentalId,
            'user_id' => $userId,
            'mes' => $mes,
            'año' => $año,
            'actividades_proyectadas' => $proyectadas,
            'actividades_ejecutadas' => $ejecutadas,
            'actividades_pendientes' => $pendientes,
            'actividades_canceladas' => $canceladas,
            'fecha_cierre' => now(),
        ]);

        // 4. Asociar actividades al cierre
        Actividad::whereIn('id', $actividades->pluck('id'))
            ->update(['cierre_mensual_id' => $cierre->id]);

        // 5. Generar PDF
        $pdf = Pdf::loadView('pdf.cierre_mensual', [
            'cierre' => $cierre,
            'actividades' => $actividades,
            'meses' => $this->meses(),
        ]);

        $pdfPath = "cierres/cierre_{$cierre->id}.pdf";
        $pdf->save(storage_path("app/public/$pdfPath"));

        $cierre->update(['pdf_path' => $pdfPath]);

        return $cierre;
    }

    private function meses()
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
    }
}
