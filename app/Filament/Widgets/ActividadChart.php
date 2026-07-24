<?php

namespace App\Filament\Widgets;

use App\Models\Actividad;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ActividadChart extends ChartWidget
{
    // Titulo que aparece
    protected static ?string $heading = 'Actividades Creadas por Mes';

    // Color del grafico
    protected static string $color = 'info';

    // Orden en el dashboard (menor numero = mas arriba)
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(5)->startOfMonth();
        $endDate = $now->copy()->endOfMonth();

        $user = auth()->user();
        $query = Actividad::query();

        if (! $user->isCentralRole()) {
            $query->where('actividads.departamental_id', $user->departamental_id);
        }

        $counts = $query->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as total")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->toArray();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $data[] = $counts[$key] ?? 0;
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
                    'display' => true, // Mostrar leyenda
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true, // Eje Y comienza en 0
                    'ticks' => [
                        'stepSize' => 1, // Intervalo de 1 en el eje Y
                    ],
                ],
            ],
        ];
    }
}
