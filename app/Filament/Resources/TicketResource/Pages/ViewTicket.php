<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use App\Models\Ticket;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Section;
use Filament\Forms;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            // Acción para cambiar estado
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
                        ->rows(3)
                        ->placeholder('Describe el motivo del cambio o acciones realizadas...'),
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
            
            // Acción rápida para cerrar ticket
            Actions\Action::make('cerrar_ticket')
                ->label('Cerrar Ticket')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('¿Estás seguro de que quieres cerrar este ticket?')
                ->form([
                    Forms\Components\Textarea::make('motivo_cierre')
                        ->label('Motivo del cierre')
                        ->required()
                        ->rows(2)
                        ->placeholder('Describe por qué se cierra el ticket...'),
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
                        
                        BadgeEntry::make('estado_interno')
                            ->label('Estado Actual')
                            ->formatStateUsing(fn (string $state): string => Ticket::ESTADOS[$state] ?? $state)
                            ->colors([
                                'warning' => fn ($state) => $this->record->estado_color === 'warning',
                                'info' => fn ($state) => $this->record->estado_color === 'info',
                                'success' => fn ($state) => $this->record->estado_color === 'success',
                                'secondary' => fn ($state) => $this->record->estado_color === 'secondary',
                                'danger' => fn ($state) => $this->record->estado_color === 'danger',
                            ]),
                        
                        BadgeEntry::make('prioridad')
                            ->label('Prioridad')
                            ->getStateUsing(fn (): string => $this->record->prioridad)
                            ->colors([
                                'danger' => 'Alta',
                                'warning' => 'Media',
                                'secondary' => 'Baja',
                            ]),
                    ])->columns(3),

                Section::make('Información del Ticket')
                    ->schema([
                        BadgeEntry::make('tipo_ticket')
                            ->label('Tipo de Ticket')
                            ->formatStateUsing(fn (string $state): string => Ticket::TIPOS[$state] ?? $state)
                            ->colors([
                                'danger' => 'SOPORTE_TECNICO',
                                'warning' => 'MANTENIMIENTO',
                                'info' => 'SOLICITUD',
                                'primary' => 'INCIDENTE',
                                'secondary' => 'RECLAMO',
                            ]),
                        
                        TextEntry::make('motivo')
                            ->label('Motivo/Asunto')
                            ->icon('heroicon-o-exclamation-triangle'),
                        
                        TextEntry::make('oficina')
                            ->label('Oficina')
                            ->icon('heroicon-o-building-office'),
                    ])->columns(3),

                Section::make('Fechas y Tiempo')
                    ->schema([
                        TextEntry::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->date('d/M/Y')
                            ->icon('heroicon-o-calendar'),
                        
                        TextEntry::make('dias_desde_creacion')
                            ->label('Días desde creación')
                            ->getStateUsing(fn (): string => $this->record->diasDesdeCreacion() . ' días')
                            ->icon('heroicon-o-clock')
                            ->color(function (): string {
                                $dias = $this->record->diasDesdeCreacion();
                                return match (true) {
                                    $dias >= 7 => 'danger',
                                    $dias >= 3 => 'warning',
                                    default => 'success'
                                };
                            }),
                        
                        TextEntry::make('tiempo_respuesta')
                            ->label('Tiempo de respuesta')
                            ->getStateUsing(function (): string {
                                if ($this->record->estaAbierto()) {
                                    return 'Pendiente de respuesta';
                                }
                                
                                $created = $this->record->created_at;
                                $updated = $this->record->updated_at;
                                $diff = $created->diff($updated);
                                
                                if ($diff->days > 0) {
                                    return $diff->days . ' días';
                                } elseif ($diff->h > 0) {
                                    return $diff->h . ' horas';
                                } else {
                                    return $diff->i . ' minutos';
                                }
                            })
                            ->icon('heroicon-o-clock'),
                    ])->columns(3),

                Section::make('Descripción y Historial')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('Descripción y Historial de Cambios')
                            ->formatStateUsing(function (?string $state): string {
                                if (empty($state)) {
                                    return 'Sin descripción';
                                }
                                
                                // Convertir saltos de línea a HTML y resaltar los cambios de estado
                                $formatted = nl2br(e($state));
                                
                                // Resaltar las líneas de cambio de estado
                                $formatted = preg_replace(
                                    '/\[(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})\] Estado cambiado a ([^:]+):?/m',
                                    '<strong style="color: #059669;">[$1] Estado cambiado a $2:</strong>',
                                    $formatted
                                );
                                
                                // Resaltar cuando se cierra
                                $formatted = preg_replace(
                                    '/\[(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2})\] Ticket CERRADO:/m',
                                    '<strong style="color: #dc2626;">[$1] Ticket CERRADO:</strong>',
                                    $formatted
                                );
                                
                                return $formatted;
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Estadísticas del Ticket')
                    ->schema([
                        TextEntry::make('esta_abierto')
                            ->label('Estado operativo')
                            ->getStateUsing(fn (): string => $this->record->estaAbierto() ? 'Abierto' : 'Cerrado')
                            ->badge()
                            ->color(fn (): string => $this->record->estaAbierto() ? 'warning' : 'success'),
                        
                        TextEntry::make('requiere_atencion')
                            ->label('Requiere atención')
                            ->getStateUsing(function (): string {
                                if (!$this->record->estaAbierto()) {
                                    return 'No aplica';
                                }
                                
                                $dias = $this->record->diasDesdeCreacion();
                                return match (true) {
                                    $dias >= 7 => 'Urgente',
                                    $dias >= 3 => 'Pronto',
                                    default => 'Normal'
                                };
                            })
                            ->badge()
                            ->color(function (): string {
                                if (!$this->record->estaAbierto()) {
                                    return 'secondary';
                                }
                                
                                $dias = $this->record->diasDesdeCreacion();
                                return match (true) {
                                    $dias >= 7 => 'danger',
                                    $dias >= 3 => 'warning',
                                    default => 'success'
                                };
                            }),
                    ])->columns(2),

                Section::make('Información del Sistema')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Creado el')
                            ->dateTime('d/M/Y H:i:s'),
                        
                        TextEntry::make('updated_at')
                            ->label('Última actualización')
                            ->dateTime('d/M/Y H:i:s')
                            ->since(),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}