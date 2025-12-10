<?php

namespace App\Http\Controllers;

use App\Models\CierreMensual;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ConsolidadoController extends Controller
{
    public function pdf($mes, $año)
    {
        $cierres = CierreMensual::where('mes', $mes)
            ->where('año', $año)
            ->with('departamental','actividades')
            ->get();

        $pdf = Pdf::loadView('pdf.consolidado', [
            'cierres' => $cierres,
            'mes' => $mes,
            'año' => $año,
            'meses' => $this->meses()
        ]);

        $filename = "consolidado/{$mes}-{$año}.pdf";

        Storage::disk('public')->put($filename, $pdf->output());

        return response()->file(storage_path("app/public/$filename"));
    }

    public function meses()
    {
        return [
            1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",
            7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"
        ];
    }
}
