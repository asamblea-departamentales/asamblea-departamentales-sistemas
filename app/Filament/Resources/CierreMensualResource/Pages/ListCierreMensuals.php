<?php

namespace App\Filament\Resources\CierreMensualResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\CierreMensualResource;
use App\Models\CierreMensual;
use App\Models\Actividad;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ListCierreMensuals extends ListRecords
{
    protected static string $resource = CierreMensualResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        return [
            Actions\Action::make('generar_cierre')
                ->label('Generar Cierre Mensual')
                ->icon('heroicon-o-archive-box')
                ->color('primary')
                ->button()
                ->modalHeading('Generar Cierre o Informe Mensual')
                ->modalDescription('Seleccione el tipo de cierre o informe que desea generar')
                ->modalSubmitActionLabel('Generar')
                ->modalWidth('2xl')
                ->form([
                    \Filament\Forms\Components\Radio::make('tipo_cierre')
                        ->label('Tipo de Cierre/Informe')
                        ->options([
                            'individual' => 'Cierre Individual (Solo mi departamental)',
                            'consolidado' => 'Informe Consolidado (Todas las departamentales)',
                        ])
                        ->default('individual')
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('mes')
                                ->label('Mes a cerrar')
                                ->options(fn () => $this->getMesesDisponibles())
                                ->default(fn () => Carbon::now()->subMonth()->month)
                                ->required(),

                            \Filament\Forms\Components\TextInput::make('año')
                                ->label('Año')
                                ->numeric()
                                ->default(fn () => Carbon::now()->subMonth()->year)
                                ->required(),
                        ]),

                    \Filament\Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones (opcional)')
                        ->rows(3)
                        ->hidden(fn (\Filament\Forms\Get $get) => $get('tipo_cierre') === 'consolidado')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $this->generarCierreMensual($data);
                })
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'gol', 'coordinador'])),

            Actions\Action::make('ir_actividades')
                ->label('Ir a Actividades')
                ->icon('heroicon-o-plus-circle')
                ->color('gray')
                ->url(fn () => ActividadResource::getUrl('index'))
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'coordinador', 'gol'])),
        ];
    }

    protected function getMesesDisponibles(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    protected function generarCierreMensual(array $data): void
    {
        $user = auth()->user();
        $mes = $data['mes'];
        $año = $data['año'];
        $tipoCierre = $data['tipo_cierre'] ?? 'individual';

        if ($tipoCierre === 'consolidado' && ! $user->hasAnyRole(['super_admin', 'gol'])) {
            Notification::make()
                ->title('Sin permisos')
                ->body('Solo SuperAdmin y GOL pueden generar informes consolidados.')
                ->danger()
                ->send();

            return;
        }

        try {
            DB::beginTransaction();

            $cierresGenerados = 0;
            $cierresOmitidos = 0;

            if ($tipoCierre === 'individual') {
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
                foreach (\App\Models\Departamental::all() as $departamental) {
                    $resultado = $this->procesarCierreDepartamental(
                        $departamental->id,
                        $mes,
                        $año,
                        $user,
                        'Cierre consolidado generado automáticamente',
                        false
                    );

                    $resultado ? $cierresGenerados++ : $cierresOmitidos++;
                }
            }

            DB::commit();

            $mensaje = $tipoCierre === 'individual'
                ? "Se ha generado el cierre de {$this->getMesesDisponibles()[$mes]} {$año}."
                : "Se generó el informe consolidado para {$this->getMesesDisponibles()[$mes]} {$año} con {$cierresGenerados} departamentales.";

            if ($cierresOmitidos > 0) {
                $mensaje .= " {$cierresOmitidos} ya existían.";
            }

            Notification::make()
                ->title('Cierre generado exitosamente')
                ->body($mensaje)
                ->success()
                ->send();

            if ($tipoCierre === 'individual' && $cierresGenerados > 0) {
                $cierre = CierreMensual::where('departamental_id', $user->departamental_id)
                    ->where('mes', $mes)
                    ->where('año', $año)
                    ->first();

                $this->redirect(route('filament.admin.resources.cierre-mensuales.view', $cierre));
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error al generar cierre')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function procesarCierreDepartamental(
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

        $actividades = Actividad::where('departamental_id', $departamentalId)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->get();

        $proyectadas = $actividades->count();
        $ejecutadas  = $actividades->where('estado', 'Completada')->count();
        $pendientes  = $actividades->where('estado', 'Pendiente')->count();
        $canceladas  = $actividades->where('estado', 'Cancelada')->count();

        $porcentajeCumplimiento = $proyectadas > 0
            ? round(($ejecutadas / $proyectadas) * 100, 2)
            : 0;

        $cierre = CierreMensual::updateOrCreate(
            [
                'departamental_id' => $departamentalId,
                'mes'              => $mes,
                'año'              => $año,
            ],
            [
                'user_id'                  => $user->id,
                'actividades_proyectadas'  => $proyectadas,
                'actividades_ejecutadas'   => $ejecutadas,
                'actividades_pendientes'   => $pendientes,
                'actividades_canceladas'   => $canceladas,
                'porcentaje_cumplimiento'  => $porcentajeCumplimiento,
                'estado'                   => 'generado',
                'observaciones'            => $observaciones,
                'fecha_cierre'             => now(),
            ]
        );

        Actividad::where('departamental_id', $departamentalId)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->update(['cierre_mensual_id' => $cierre->id]);

        if ($generarPDF) {
            $cierre->generarPDF();
        }

        return true;
    }
}