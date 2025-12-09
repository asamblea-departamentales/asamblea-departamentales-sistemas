<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RequisicionResource\Pages;
use App\Models\Requisicion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequisicionResource extends Resource
{
    protected static ?string $model = Requisicion::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Bitacoras';
    protected static ?string $navigationLabel = 'Requisiciones';
    protected static ?string $pluralModelLabel = 'Requisiciones';

    // Badge con contador
    public static function getNavigationBadge(): ?string
{
    $user = \Filament\Facades\Filament::auth()->user();

    $isCentral = $user && (
        $user->hasRole(['ti','Administrador','gol']) || // puedes usar hasRole con array
        $user->hasRole(config('filament-shield.super_admin.name'))
    );

    $query = static::getModel()::pendientes();

    if (! $isCentral && $user) {
        $query->where('departamental_id', $user->departamental_id);
    }

    return (string) $query->count();
}


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Insumo')
                    ->schema([
                        Forms\Components\Select::make('tipo_insumo')
                            ->label('Tipo de Insumo')
                            ->options(Requisicion::TIPOS_INSUMO)
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('rubro')
                            ->label('Rubro')
                            ->options(Requisicion::RUBROS)
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('unidades'),
                    ])->columns(3),

                Forms\Components\Section::make('Información de Solicitud')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_solicitud')
                            ->label('Fecha de Solicitud')
                            ->required()
                            ->default(now())
                            ->native(false),
                        
                        Forms\Components\Select::make('estado_interno')
                            ->label('Estado Interno')
                            ->options(Requisicion::ESTADOS)
                            ->default('SOLICITADA')
                            ->required(),
                        
                        Forms\Components\TextInput::make('oficina')
                            ->label('Oficina')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_insumo')
                    ->label('Tipo de Insumo')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => Requisicion::TIPOS_INSUMO[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('rubro')
                    ->label('Rubro')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => Requisicion::RUBROS[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable()
                    ->numeric()
                    ->suffix(' unidades'),
                
                Tables\Columns\TextColumn::make('fecha_solicitud')
                    ->label('Fecha Solicitud')
                    ->date('d/M/Y')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('estado_interno')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state): string => Requisicion::ESTADOS[$state] ?? $state)
                    ->colors([
                        'primary' => 'SOLICITADA',
                        'warning' => 'EN_REVISION',
                        'info' => 'APROBADA',
                        'danger' => 'RECHAZADA',
                        'success' => 'COMPRADA',
                        'secondary' => 'ENTREGADA',
                    ]),
                
                Tables\Columns\BadgeColumn::make('urgencia')
                    ->label('Urgencia')
                    ->getStateUsing(fn (Requisicion $record): string => $record->urgencia)
                    ->colors([
                        'danger' => 'Urgente',
                        'warning' => 'Alta',
                        'primary' => 'Media',
                        'secondary' => 'Baja',
                    ]),
                
                Tables\Columns\TextColumn::make('oficina')
                    ->label('Oficina')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/M/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado_interno')
                    ->label('Estado')
                    ->options(Requisicion::ESTADOS),
                
                SelectFilter::make('tipo_insumo')
                    ->label('Tipo de Insumo')
                    ->options(Requisicion::TIPOS_INSUMO),
                
                SelectFilter::make('rubro')
                    ->label('Rubro')
                    ->options(Requisicion::RUBROS),
                
                SelectFilter::make('oficina')
                    ->label('Oficina')
                    ->options(function () {
                        return Requisicion::distinct('oficina')
                            ->pluck('oficina', 'oficina')
                            ->toArray();
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // Acción personalizada para cambiar estado
                Tables\Actions\Action::make('cambiar_estado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('nuevo_estado')
                            ->label('Nuevo Estado')
                            ->options(function (Requisicion $record) {
                                $siguientes = $record->getSiguientesEstadosPosibles();
                                return collect(Requisicion::ESTADOS)
                                    ->filter(fn ($label, $key) => in_array($key, $siguientes))
                                    ->toArray();
                            })
                            ->required(),
                        
                        Forms\Components\Textarea::make('observacion')
                            ->label('Observación del cambio')
                            ->rows(2),
                    ])
                    ->action(function (array $data, Requisicion $record): void {
                        $record->update([
                            'estado_interno' => $data['nuevo_estado'],
                            'observaciones' => $record->observaciones . 
                                "\n[" . now()->format('d/m/Y H:i') . "] Estado cambiado a " . 
                                Requisicion::ESTADOS[$data['nuevo_estado']] . 
                                (isset($data['observacion']) ? ": " . $data['observacion'] : "")
                        ]);
                    })
                    ->visible(fn (Requisicion $record) => !empty($record->getSiguientesEstadosPosibles())),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRequisicions::route('/'),
            'create' => Pages\CreateRequisicion::route('/create'),
            'view' => Pages\ViewRequisicion::route('/{record}'),
            'edit' => Pages\EditRequisicion::route('/{record}/edit'),
        ];
    }
}