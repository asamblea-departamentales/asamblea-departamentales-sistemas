<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Forms;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('cambiar_estado')
                ->label('Cambiar Estado')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('nuevo_estado')
                        ->label('Nuevo Estado')
                        ->options(Ticket::ESTADOS)
                        ->required()
                        ->default(fn () => $this->record->estado_interno),

                    Forms\Components\Textarea::make('comentario')
                        ->label('Comentario del cambio')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'estado_interno' => $data['nuevo_estado'],
                        'observaciones' => $this->record->observaciones .
                            "\n\n[" . now()->format('d/m/Y H:i') . "] Estado cambiado a " .
                            Ticket::ESTADOS[$data['nuevo_estado']] .
                            (isset($data['comentario']) ? ": " . $data['comentario'] : "")
                    ]);

                    $this->refreshFormData(['estado_interno', 'observaciones']);
                }),

            Actions\Action::make('cerrar_ticket')
                ->label('Cerrar Ticket')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    Forms\Components\Textarea::make('motivo_cierre')
                        ->label('Motivo del cierre')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'estado_interno' => 'CERRADO',
                        'observaciones' => $this->record->observaciones .
                            "\n\n[" . now()->format('d/m/Y H:i') . "] Ticket CERRADO: " . $data['motivo_cierre']
                    ]);

                    $this->refreshFormData(['estado_interno', 'observaciones']);
                })
                ->visible(fn () => $this->record->estaAbierto()),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Estado del Ticket')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Ticket ID')
                            ->copyable(),

                        TextEntry::make('estado_interno')
                            ->label('Estado Actual')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Ticket::ESTADOS[$state] ?? $state)
                            ->color(fn () => $this->record->estado_color),

                        TextEntry::make('prioridad')
                            ->label('Prioridad')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Alta' => 'danger',
                                'Media' => 'warning',
                                default => 'secondary'
                            }),
                    ])->columns(3),

                Section::make('Información del Ticket')
                    ->schema([
                        TextEntry::make('tipo_ticket')
                            ->label('Tipo de Ticket')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => Ticket::TIPOS[$state] ?? $state)
                            ->color(fn ($state) => match ($state) {
                                'SOPORTE_TECNICO' => 'danger',
                                'MANTENIMIENTO' => 'warning',
                                'SOLICITUD' => 'info',
                                'INCIDENTE' => 'primary',
                                default => 'secondary'
                            }),

                        TextEntry::make('motivo')->label('Motivo/Asunto'),
                        TextEntry::make('departamental.nombre')->label('Departamental'),
                    ])->columns(3),

                Section::make('Fechas y Tiempo')
                    ->schema([
                        TextEntry::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->date('d/M/Y'),

                        TextEntry::make('dias_desde_creacion')
                            ->label('Días desde creación')
                            ->badge()
                            ->color(fn () => match (true) {
                                $this->record->diasDesdeCreacion() >= 7 => 'danger',
                                $this->record->diasDesdeCreacion() >= 3 => 'warning',
                                default => 'success'
                            }),

                        TextEntry::make('tiempo_respuesta')
                            ->label('Tiempo de respuesta')
                            ->getStateUsing(function (): string {
                                if ($this->record->estaAbierto()) {
                                    return 'Pendiente de respuesta';
                                }

                                $diff = $this->record->created_at->diff($this->record->updated_at);

                                return $diff->days > 0
                                    ? $diff->days . ' días'
                                    : ($diff->h > 0 ? $diff->h . ' horas' : $diff->i . ' minutos');
                            }),
                    ])->columns(3),

                Section::make('Descripción y Historial')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('Descripción y Historial de Cambios')
                            ->html()
                            ->formatStateUsing(function (?string $state) {
                                if (!$state) return 'Sin descripción';

                                $formatted = nl2br(e($state));

                                $formatted = preg_replace(
                                    '/\[(.*?)\] Estado cambiado a (.*?):?/m',
                                    '<strong style="color:#059669;">[$1] Estado cambiado a $2:</strong>',
                                    $formatted
                                );

                                $formatted = preg_replace(
                                    '/\[(.*?)\] Ticket CERRADO:/m',
                                    '<strong style="color:#dc2626;">[$1] Ticket CERRADO:</strong>',
                                    $formatted
                                );

                                return $formatted;
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Estadísticas del Ticket')
                    ->schema([
                        TextEntry::make('esta_abierto')
                            ->label('Estado operativo')
                            ->badge()
                            ->color(fn () => $this->record->estaAbierto() ? 'warning' : 'success')
                            ->getStateUsing(fn () => $this->record->estaAbierto() ? 'Abierto' : 'Cerrado'),

                        TextEntry::make('requiere_atencion')
                            ->label('Requiere atención')
                            ->badge()
                            ->getStateUsing(function () {
                                if (!$this->record->estaAbierto()) return 'No aplica';
                                return match (true) {
                                    $this->record->diasDesdeCreacion() >= 7 => 'Urgente',
                                    $this->record->diasDesdeCreacion() >= 3 => 'Pronto',
                                    default => 'Normal'
                                };
                            })
                            ->color(function () {
                                if (!$this->record->estaAbierto()) return 'secondary';
                                return match (true) {
                                    $this->record->diasDesdeCreacion() >= 7 => 'danger',
                                    $this->record->diasDesdeCreacion() >= 3 => 'warning',
                                    default => 'success'
                                };
                            }),
                    ])->columns(2),

                Section::make('Información del Sistema')
                    ->schema([
                        TextEntry::make('created_at')->label('Creado el')->dateTime('d/M/Y H:i:s'),
                        TextEntry::make('updated_at')->label('Última actualización')->dateTime('d/M/Y H:i:s')->since(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
