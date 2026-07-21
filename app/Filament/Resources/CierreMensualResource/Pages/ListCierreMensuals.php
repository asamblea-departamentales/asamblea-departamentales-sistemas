<?php

namespace App\Filament\Resources\CierreMensualResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Filament\Resources\CierreMensualResource;
use App\Models\CierreMensual;
use App\Services\CierreMensualService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListCierreMensuals extends ListRecords
{
    protected static string $resource = CierreMensualResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $service = app(CierreMensualService::class);

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
                                ->options(fn () => $service->getMesesDisponibles())
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

    protected function generarCierreMensual(array $data): void
    {
        $user = auth()->user();
        $service = app(CierreMensualService::class);
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

            $resultado = $service->generarCierre($data, $user);

            DB::commit();

            $mensaje = $tipoCierre === 'individual'
                ? "Se ha generado el cierre de {$service->getNombreMes($mes)} {$año}."
                : "Se generó el informe consolidado para {$service->getNombreMes($mes)} {$año} con {$resultado['generados']} departamentales.";

            if ($resultado['omitidos'] > 0) {
                $mensaje .= " {$resultado['omitidos']} ya existían.";
            }

            Notification::make()
                ->title('Cierre generado exitosamente')
                ->body($mensaje)
                ->success()
                ->send();

            if ($tipoCierre === 'individual' && $resultado['generados'] > 0) {
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
                ->body('Ocurrió un error: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
