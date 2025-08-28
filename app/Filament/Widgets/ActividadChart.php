<?php

namespace App\Filament\Widgets;

use App\Models\Actividad;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ActividadChart extends ChartWidget
{
    //Titulo que aparece
    protected static ?string $heading = 'Actividades Creadas por Mes';

    //Color del grafico
    protected static string $color = 'info';

    //Orden en el dashboard (menor numero = mas arriba)
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        //Generar datos para los ultimos meses
        for ($i = 5; $i >= 0; $i--)
        {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y'); // Formato de mes y año Eje: Jul 2025

            //Consultar la DB
            $count = Actividad::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();

            $data[] = $count; //Se guardan los datos    
        }
        return [
            'datasets' => [
                [
                    'label' => 'Actividades Creadas',
                    'data' => $data, // Datos reales desde BD
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(59, 130, 246, 1)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true, //Mostrar leyenda
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true, //Eje Y comienza en 0
                    'ticks' => [
                        'stepSize' => 1, //Intervalo de 1 en el eje Y
                    ],
                ],
            ],
        ];
    }
}
