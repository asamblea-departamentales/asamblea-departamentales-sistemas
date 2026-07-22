<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Models\CierreMensual;
use App\Services\CierreMensualService;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListActividades extends ListRecords
{
    protected static string $resource = ActividadResource::class;

    protected function getHeaderActions(): array
    {
        $user = auth()->user();
        $service = app(CierreMensualService::class);

        return [
            Actions\CreateAction::make()
                ->label('Crear Actividad')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Actions\Action::make('descargar_consolidado')
                ->label('Descargar Consolidado')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->button()
                ->visible(fn () => $user && $user->hasAnyRole(['super_admin', 'gol', 'coordinador']))
                ->modalWidth('md')
                ->modalHeading('Descargar Informe Consolidado')
                ->form([
                    \Filament\Forms\Components\Select::make('descargar_mes')
                        ->label('Mes')
                        ->options($service->getMesesDisponibles())
                        ->default(Carbon::now()->subMonth()->month)
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('descargar_año')
                        ->label('Año')
                        ->numeric()
                        ->default(Carbon::now()->subMonth()->year)
                        ->required(),
                ])
                ->action(function (array $data) use ($service) {
                    $año = $data['descargar_año'];
                    $mes = $data['descargar_mes'];
                    $filename = "informe_consolidado_{$año}_{$mes}.pdf";
                    $path = storage_path("app/public/cierres/{$filename}");

                    if (! file_exists($path)) {
                        Notification::make()
                            ->title('PDF no encontrado')
                            ->body("El consolidado de {$service->getNombreMes($mes)} $año no ha sido generado. Genérelo primero desde 'Generar Cierre Mensual'.")
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Descargando')
                        ->body('El PDF se abrirá en una nueva pestaña')
                        ->success()
                        ->send();

                    return redirect()->route('consolidado.pdf', ['año' => $año, 'mes' => $mes]);
                }),

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
                $departamentalId = $user->isSuperAdmin() ? $resultado['cierres'][0]->departamental_id ?? null : $user->departamental_id;

                if ($departamentalId) {
                    $cierre = CierreMensual::where('departamental_id', $departamentalId)
                        ->where('mes', $mes)
                        ->where('año', $año)
                        ->first();

                    if ($cierre) {
                        $this->redirect(route('filament.admin.resources.cierre-mensuales.view', $cierre));
                        return;
                    }
                }
            }

            $this->redirect(\App\Filament\Resources\CierreMensualResource::getUrl('index'));

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
