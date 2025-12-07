<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Models\Actividad;
use App\Models\CierreMensual;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListActividades extends ListRecords
{
    protected static string $resource = ActividadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Crear Actividad')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),

            Actions\Action::make('generar_cierre')
                ->label('Generar Cierre Mensual')
                ->icon('heroicon-o-archive-box')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Generar Cierre o Informe Mensual')
                ->modalDescription('Seleccione el tipo de cierre o informe que desea generar')
                ->modalSubmitActionLabel('Generar')
                ->modalWidth('2xl')
                
                // Formulario
                ->form([
                    \Filament\Forms\Components\Radio::make('tipo_cierre')
                        ->label('Tipo de Cierre/Informe')
                        ->options([
                            'individual' => 'Cierre Individual (Solo mi departamental)',
                            'consolidado' => 'Informe Consolidado (Todas las departamentales)',
                        ])
                        ->default('individual')
                        ->required()
                        ->descriptions([
                            'individual' => 'Genera el cierre solo para su departamental',
                            'consolidado' => 'Genera un informe consolidado de todas las departamentales (Solo SuperAdmin y GOL)',
                        ])
                        ->live()
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Select::make('mes')
                        ->label('Mes a cerrar')
                        ->options($this->getMesesDisponibles())
                        ->default(Carbon::now()->subMonth()->month)
                        ->required()
                        ->columnSpan(1),
                    
                    \Filament\Forms\Components\TextInput::make('año')
                        ->label('Año')
                        ->numeric()
                        ->default(Carbon::now()->subMonth()->year)
                        ->required()
                        ->columnSpan(1),
                    
                    \Filament\Forms\Components\Textarea::make('observaciones')
                        ->label('Observaciones (opcional)')
                        ->rows(3)
                        ->visible(fn ($get) => $get('tipo_cierre') === 'individual')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                
                ->action(function (array $data) {
                    $this->generarCierreMensual($data);
                })
                
                ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'gol', 'coordinador'])),
        ];
    }

    // Función para obtener los meses disponibles
    protected function getMesesDisponibles(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    // Función para obtener el nombre del mes anterior
    protected function getMesAnteriorNombre(): string
    {
        $meses = $this->getMesesDisponibles();
        return $meses[Carbon::now()->subMonth()->month];
    }

    // Función principal para generar el cierre mensual
    protected function generarCierreMensual(array $data): void
    {
        $user = auth()->user();
        $mes = $data['mes'];
        $año = $data['año'];
        $tipoCierre = $data['tipo_cierre'] ?? 'individual';

        // Validar permisos para consolidado
        if ($tipoCierre === 'consolidado' && !$user->hasAnyRole(['super_admin', 'gol'])) {
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

            // Si es informe individual → solo la departamental del usuario
            if ($tipoCierre === 'individual') {
                $resultado = $this->procesarCierreDepartamental(
                    $user->departamental_id, 
                    $mes, 
                    $año, 
                    $user, 
                    $data['observaciones'] ?? null
                );
                
                if ($resultado) {
                    $cierresGenerados++;
                } else {
                    $cierresOmitidos++;
                }
            }

            // Si es informe consolidado → recorrer todas las departamentales
            if ($tipoCierre === 'consolidado') {
                foreach (\App\Models\Departamental::all() as $departamental) {
                    $resultado = $this->procesarCierreDepartamental(
                        $departamental->id, 
                        $mes, 
                        $año, 
                        $user, 
                        'Cierre consolidado generado automáticamente'
                    );
                    
                    if ($resultado) {
                        $cierresGenerados++;
                    } else {
                        $cierresOmitidos++;
                    }
                }
            }

            DB::commit();

            $mensaje = $tipoCierre === 'individual'
                ? "Se ha generado el cierre de {$this->getMesesDisponibles()[$mes]} {$año}."
                : "Se generaron {$cierresGenerados} cierres para {$this->getMesesDisponibles()[$mes]} {$año}.";

            if ($cierresOmitidos > 0) {
                $mensaje .= " {$cierresOmitidos} ya existían.";
            }

            Notification::make()
                ->title('Cierre generado exitosamente')
                ->body($mensaje)
                ->success()
                ->send();

            // Redirigir según el tipo
            if ($tipoCierre === 'individual' && $cierresGenerados > 0) {
                $cierre = CierreMensual::where('departamental_id', $user->departamental_id)
                    ->where('mes', $mes)
                    ->where('año', $año)
                    ->first();
                    
                $this->redirect(route('filament.admin.resources.cierre-mensuales.view', $cierre));
            } else {
                $this->redirect(\App\Filament\Resources\CierreMensualResource::getUrl('index'));
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

    /**
     * Función auxiliar para procesar el cierre de una departamental
     * @return bool True si se generó el cierre, False si ya existía
     */
    protected function procesarCierreDepartamental(
        int $departamentalId, 
        int $mes, 
        int $año, 
        $user, 
        ?string $observaciones
    ): bool {
        // Verificar si ya existe un cierre para este mes
        $cierreExistente = CierreMensual::where('departamental_id', $departamentalId)
            ->where('mes', $mes)
            ->where('año', $año)
            ->first();

        if ($cierreExistente && $cierreExistente->estado !== 'reabierto') {
            return false; // Ya existe, no se generó uno nuevo
        }

        // Obtener actividades del mes
        $actividades = Actividad::where('departamental_id', $departamentalId)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->get();

        // Calcular métricas
        $proyectadas = $actividades->count();
        $ejecutadas = $actividades->where('estado', 'Completada')->count();
        $pendientes = $actividades->where('estado', 'Pendiente')->count();
        $canceladas = $actividades->where('estado', 'Cancelada')->count();
        
        $porcentajeCumplimiento = $proyectadas > 0
            ? round(($ejecutadas / $proyectadas) * 100, 2)
            : 0;

        // Crear o actualizar el cierre
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
                'actividades_pendientes' => $pendientes,
                'actividades_canceladas' => $canceladas,
                'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                'estado' => 'generado',
                'observaciones' => $observaciones,
                'fecha_cierre' => now(),
            ]
        );

        // Vincular actividades del mes al cierre
        Actividad::where('departamental_id', $departamentalId)
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $año)
            ->update(['cierre_mensual_id' => $cierre->id]);

        // Generar PDF con actividades ya vinculadas
        $cierre->generarPDF();

        return true; // Se generó exitosamente
    }
}