<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use App\Models\Actividad; //NUEVO
use App\Models\CierreMensual; //NUEVO
use Filament\Actions;
use Filament\Notifications\Notification; //NUEVO
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB; //NUEVO
use Carbon\Carbon; //NUEVO
use Illuminate\Support\Arr;

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

            // NUEVO: Acción personalizada para generar cierre mensual
           Actions\Action::make('generar_cierre')
            ->label('Generar Cierre Mensual')
            ->icon('heroicon-o-archive-box')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Generar Cierre Mensual')
            ->modalDescription(fn () =>
            'Se genererá el cierre del mes ' . $this->getMesAnteriorNombre() . ' ' .
            Carbon::now()->subMonth()->year . 
            '. Esta accion consolidará las actividades y no podrá ser revertida.')
            ->modalSubmitActionLabel('Generar Cierre')
            //Formulario
            ->form([
                \Filament\Forms\Components\Select::make('mes')
                    ->label('Mes a cerrar')
                    ->options($this->getMesesDisponibles())
                    ->default(Carbon::now()->subMonth()->month)
                    ->required(),
                
                \Filament\Forms\Components\TextInput::make('año')
                    ->label('Año')
                    ->numeric()
                    ->default(Carbon::now()->subMonth()->year)
                    ->required(),
                
                \Filament\Forms\Components\Textarea::make('observaciones')
                    ->label('Observaciones (opcional)')
                    ->rows(3),
            ])
            ->action(function (array $data){
                $this->generarCierreMensual($data);
            })  
            ->visible(fn () => auth()->user()->hasAnyRole(['super_admin', 'gol', 'coordinador'])),
        ];
    }

    //NUEVO: Funcion para obtener los meses disponibles
    protected function getMesesDisponibles(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
            4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
            10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    //NUEVO: Funcion para obtener el nombre del mes anterior
    protected function getMesAnteriorNombre(): string
    {
        $meses = $this->getMesesDisponibles();
        return $meses[Carbon::now()->subMonth()->month];
    }

    //NUEVO: Funcion para generar el cierre mensual
    protected function generarCierreMensual(array $data): void
    {
        $user = auth()->user();
        $mes = $data['mes'];
        $año = $data['año'];
        $departamentalId = $user->departamental_id;

        // Verificar si ya existe un cierre para este mes
        $cierreExistente = CierreMensual::where('departamental_id', $departamentalId)
            ->where('mes', $mes)
            ->where('año', $año)
            ->first();

        if ($cierreExistente && $cierreExistente->estado !== 'reabierto') {
            Notification::make()
                ->title('Cierre ya existe')
                ->body('Ya existe un cierre para este mes y año.')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

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
        'observaciones' => $data['observaciones'] ?? null,
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

            DB::commit();

            Notification::make()
                ->title('Cierre generado exitosamente')
                ->body("Se ha generado el cierre de {$this->getMesesDisponibles()[$mes]} {$año}. Total de actividades: {$proyectadas}, Ejecutadas: {$ejecutadas} ({$porcentajeCumplimiento}%)")
                ->success()
                ->send();

            // Redirigir al cierre creado
            $this->redirect(route('filament.admin.resources.cierre-mensuales.view', $cierre));

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error al generar cierre')
                ->body('Ocurrió un error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
}
