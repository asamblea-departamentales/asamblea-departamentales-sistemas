<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Bitacoras';
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?string $pluralModelLabel = 'Tickets';

    // Badge con contador de tickets abiertos
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::abiertos()->count();
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Información del Ticket')
                ->schema([
                    Forms\Components\Select::make('tipo_ticket')
                        ->label('Tipo de Ticket')
                        ->options(Ticket::TIPOS)
                        ->required()
                        ->searchable(),
                    
                    Forms\Components\TextInput::make('motivo')
                        ->label('Motivo/Asunto')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),
                ])->columns(3),

            Forms\Components\Section::make('Gestión del Ticket')
                ->schema([
                    Forms\Components\DatePicker::make('fecha_solicitud')
                        ->label('Fecha de Solicitud')
                        ->required()
                        ->default(now())
                        ->native(false),
                    
                    Forms\Components\Select::make('estado_interno')
                        ->label('Estado')
                        ->options(Ticket::ESTADOS)
                        ->default('PENDIENTE')
                        ->required(),
                    
                        Forms\Components\TextInput::make('oficina')
                        ->label('Departamental')
                        ->disabled()
                        ->afterStateHydrated(fn ($component, $state) => 
                            $component->state($state ?? (auth()->user()->departamental ?? 'No especificada'))
                        )
                        ->dehydrated(true),
                    
                ])->columns(3),

            Forms\Components\Section::make('Descripción y Observaciones')
                ->schema([
                    Forms\Components\Textarea::make('observaciones')
                        ->label('Descripción del problema/solicitud')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('tipo_ticket')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => Ticket::TIPOS[$state] ?? $state)
                    ->colors([
                        'danger' => 'SOPORTE_TECNICO',
                        'warning' => 'MANTENIMIENTO',
                        'info' => 'SOLICITUD',
                        'primary' => 'INCIDENTE',
                        'secondary' => 'RECLAMO',
                    ]),
                
                Tables\Columns\TextColumn::make('motivo')
                    ->label('Motivo/Asunto')
                    ->sortable()
                    ->searchable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('Fecha')
                    ->date('d/M/Y')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('estado_interno')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => Ticket::ESTADOS[$state] ?? $state)
                    ->getStateUsing(fn (Ticket $record): string => $record->estado_color)
                    ->colors([
                        'warning' => 'warning',
                        'info' => 'info',
                        'success' => 'success',
                        'secondary' => 'secondary',
                        'danger' => 'danger',
                    ]),
                
                Tables\Columns\BadgeColumn::make('prioridad')
                    ->label('Prioridad')
                    ->getStateUsing(fn (Ticket $record): string => $record->prioridad)
                    ->colors([
                        'danger' => 'Alta',
                        'warning' => 'Media',
                        'secondary' => 'Baja',
                    ]),
                
                    Tables\Columns\TextColumn::make('oficina')
                    ->label('Departamental')
                    ->formatStateUsing(fn ($state) => is_array($state) ? ($state['nombre'] ?? 'N/A') : $state)
                    ->sortable()
                    ->searchable(),
                
                
                    Tables\Columns\TextColumn::make('dias_creacion')
    ->label('Días')
    ->getStateUsing(fn (Ticket $record): string =>
        $record->diasDesdeCreacion() === 0 
            ? 'Hoy' 
            : $record->diasDesdeCreacion() . ' días'
    )
    ->sortable(false)
    ->searchable(false),

                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo_ticket')
                    ->label('Tipo de Ticket')
                    ->options(Ticket::TIPOS),
                
                SelectFilter::make('estado_interno')
                    ->label('Estado')
                    ->options(Ticket::ESTADOS),
                
                SelectFilter::make('oficina')
                    ->label('Oficina')
                    ->options(function () {
                        return Ticket::distinct('oficina')
                            ->pluck('oficina', 'oficina')
                            ->toArray();
                    }),
                
                Filter::make('abiertos')
                    ->label('Solo Abiertos')
                    ->query(fn (Builder $query): Builder => $query->abiertos()),
                
                Filter::make('cerrados')
                    ->label('Solo Cerrados')
                    ->query(fn (Builder $query): Builder => $query->cerrados()),
                
                Filter::make('alta_prioridad')
                    ->label('Alta Prioridad')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('fecha_solicitud', '<=', now()->subDays(7))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // Acción para cambiar estado
                Tables\Actions\Action::make('cambiar_estado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('nuevo_estado')
                            ->label('Nuevo Estado')
                            ->options(Ticket::ESTADOS)
                            ->required()
                            ->default(fn (Ticket $record) => $record->estado_interno),
                        
                        Forms\Components\Textarea::make('comentario')
                            ->label('Comentario del cambio')
                            ->rows(2),
                    ])
                    ->action(function (array $data, Ticket $record): void {
                        $record->update([
                            'estado_interno' => $data['nuevo_estado'],
                            'observaciones' => $record->observaciones . 
                                "\n\n[" . now()->format('d/m/Y H:i') . "] Estado cambiado a " . 
                                Ticket::ESTADOS[$data['nuevo_estado']] . 
                                (isset($data['comentario']) ? ": " . $data['comentario'] : "")
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // Acción masiva para cerrar tickets
                    Tables\Actions\BulkAction::make('cerrar_tickets')
                        ->label('Cerrar Tickets')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'estado_interno' => 'CERRADO',
                                    'observaciones' => $record->observaciones . 
                                        "\n[" . now()->format('d/m/Y H:i') . "] Ticket cerrado masivamente"
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}