<?php

namespace App\Filament\Resources\ContratoResource\Pages;

use App\Filament\Resources\ContratoResource;
use App\Models\Contrato;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\BadgeEntry;
use Filament\Infolists\Components\Section;

class ViewContrato extends ViewRecord
{
    protected static string $resource = ContratoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Estado del Contrato')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID')
                            ->copyable(),
                        
                        BadgeEntry::make('estado')
                            ->label('Estado Actual')
                            ->getStateUsing(fn (): string => $this->record->estado)
                            ->colors([
                                'success' => 'Vigente',
                                'warning' => 'Por vencer',
                                'danger' => 'Vencido',
                            ]),
                        
                        TextEntry::make('dias_vencimiento')
                            ->label('Días para vencimiento')
                            ->getStateUsing(function (): string {
                                $dias = $this->record->diasParaVencimiento();
                                if ($dias < 0) {
                                    return abs($dias) . ' días vencido';
                                } elseif ($dias == 0) {
                                    return 'Vence hoy';
                                } else {
                                    return $dias . ' días restantes';
                                }
                            })
                            ->color(function (): string {
                                $dias = $this->record->diasParaVencimiento();
                                return match (true) {
                                    $dias < 0 => 'danger',
                                    $dias <= 30 => 'warning',
                                    default => 'success'
                                };
                            }),
                    ])->columns(3),

                Section::make('Información del Contrato')
                    ->schema([
                        BadgeEntry::make('tipo')
                            ->label('Tipo de Contrato')
                            ->colors([
                                'primary' => 'SERVICIOS',
                                'success' => 'SUMINISTROS',
                                'warning' => 'OBRAS',
                                'info' => 'CONSULTORIA',
                                'secondary' => 'MANTENIMIENTO',
                                'danger' => 'ARRENDAMIENTO',
                            ]),
                        
                        TextEntry::make('proveedor')
                            ->label('Proveedor/Contratista')
                            ->icon('heroicon-o-building-storefront'),
                        
                        TextEntry::make('monto')
                            ->label('Monto del Contrato')
                            ->getStateUsing(fn (): string => $this->record->monto_formateado)
                            ->icon('heroicon-o-currency-dollar'),
                    ])->columns(3),

                Section::make('Vigencia y Ubicación')
                    ->schema([
                        TextEntry::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->date('d/M/Y')
                            ->icon('heroicon-o-play'),
                        
                        TextEntry::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->date('d/M/Y')
                            ->icon('heroicon-o-stop')
                            ->color(fn (): string => match (true) {
                                !$this->record->estaVigente() => 'danger',
                                $this->record->diasParaVencimiento() <= 30 => 'warning',
                                default => 'success'
                            }),
                        
                        TextEntry::make('oficina')
                            ->label('Oficina Responsable')
                            ->icon('heroicon-o-building-office'),
                        
                        TextEntry::make('duracion')
                            ->label('Duración del Contrato')
                            ->getStateUsing(function (): string {
                                $inicio = $this->record->fecha_inicio;
                                $fin = $this->record->fecha_vencimiento;
                                $dias = $inicio->diffInDays($fin);
                                
                                if ($dias >= 365) {
                                    $anos = floor($dias / 365);
                                    $diasRestantes = $dias % 365;
                                    return $anos . ' año' . ($anos > 1 ? 's' : '') . 
                                           ($diasRestantes > 0 ? ' y ' . $diasRestantes . ' días' : '');
                                } elseif ($dias >= 30) {
                                    $meses = floor($dias / 30);
                                    $diasRestantes = $dias % 30;
                                    return $meses . ' mes' . ($meses > 1 ? 'es' : '') . 
                                           ($diasRestantes > 0 ? ' y ' . $diasRestantes . ' días' : '');
                                } else {
                                    return $dias . ' día' . ($dias != 1 ? 's' : '');
                                }
                            })
                            ->icon('heroicon-o-clock'),
                    ])->columns(2),

                Section::make('Observaciones')
                    ->schema([
                        TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->formatStateUsing(function (?string $state): string {
                                if (empty($state)) {
                                    return 'Sin observaciones';
                                }
                                return nl2br(e($state));
                            })
                            ->html()
                            ->columnSpanFull(),
                    ]),

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