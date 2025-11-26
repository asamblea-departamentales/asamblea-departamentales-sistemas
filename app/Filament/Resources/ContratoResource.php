<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContratoResource\Pages;
use App\Models\Contrato;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ContratoResource extends Resource
{
    protected static ?string $model = Contrato::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Bitacoras';
    protected static ?string $navigationLabel = 'Contratos';
    protected static ?string $pluralModelLabel = 'Contratos';

    // Badge con contador de contratos por vencer
    public static function getNavigationBadge(): ?string
    {
        $porVencer = static::getModel()::vigentes()
            ->where('fecha_vencimiento', '<=', now()->addDays(30))
            ->count();
        return $porVencer > 0 ? (string) $porVencer : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Contrato')
                    ->schema([
                        Forms\Components\Select::make('tipo')
    ->label('Tipo de Contrato')
    ->required()
    ->options([
        'SERVICIOS'     => 'Servicios',
        'SUMINISTROS'   => 'Suministros',
        'OBRAS'         => 'Obras',
        'CONSULTORIA'   => 'Consultoría',
        'MANTENIMIENTO' => 'Mantenimiento',
        'ARRENDAMIENTO' => 'Arrendamiento',
    ])
    ->native(false) // ← usa el select nativo del navegador
    ->placeholder('Seleccione un tipo de contrato'),

                        
                        Forms\Components\TextInput::make('proveedor')
                            ->label('Proveedor/Contratista')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('monto')
                            ->label('Monto del Contrato')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01),
                    ])->columns(3),

                Forms\Components\Section::make('Vigencia del Contrato')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_inicio')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->native(false),
                        
                        Forms\Components\DatePicker::make('fecha_vencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->native(false)
                            ->after('fecha_inicio'),
                        
                        Forms\Components\TextInput::make('oficina')
                            ->label('Oficina Responsable')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Forms\Components\Section::make('Observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'SERVICIOS',
                        'success' => 'SUMINISTROS',
                        'warning' => 'OBRAS',
                        'info' => 'CONSULTORIA',
                        'secondary' => 'MANTENIMIENTO',
                        'danger' => 'ARRENDAMIENTO',
                    ])
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('proveedor')
                    ->label('Proveedor')
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_inicio')
                    ->label('Inicio')
                    ->date('d/M/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/M/Y')
                    ->sortable()
                    ->color(fn (Contrato $record): string => match (true) {
                        !$record->estaVigente() => 'danger',
                        $record->diasParaVencimiento() <= 30 => 'warning',
                        default => 'success'
                    }),
                
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->getStateUsing(fn (Contrato $record): string => $record->estado)
                    ->colors([
                        'success' => 'Vigente',
                        'warning' => 'Por vencer',
                        'danger' => 'Vencido',
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
                SelectFilter::make('tipo')
                    ->label('Tipo de Contrato')
                    ->options([
                        'SERVICIOS' => 'Servicios',
                        'SUMINISTROS' => 'Suministros',
                        'OBRAS' => 'Obras',
                        'CONSULTORIA' => 'Consultoría',
                        'MANTENIMIENTO' => 'Mantenimiento',
                        'ARRENDAMIENTO' => 'Arrendamiento',
                    ]),
                
                SelectFilter::make('oficina')
                    ->label('Oficina')
                    ->options(function () {
                        return Contrato::distinct('oficina')
                            ->pluck('oficina', 'oficina')
                            ->toArray();
                    }),
                
                Filter::make('vigentes')
                    ->label('Solo Vigentes')
                    ->query(fn (Builder $query): Builder => $query->vigentes()),
                
                Filter::make('vencidos')
                    ->label('Solo Vencidos')
                    ->query(fn (Builder $query): Builder => $query->vencidos()),
                
                Filter::make('por_vencer')
                    ->label('Por Vencer (30 días)')
                    ->query(fn (Builder $query): Builder => 
                        $query->vigentes()
                            ->where('fecha_vencimiento', '<=', now()->addDays(30))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('fecha_vencimiento', 'asc');
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
            'index' => Pages\ListContratos::route('/'),
            'create' => Pages\CreateContrato::route('/create'),
            'view' => Pages\ViewContrato::route('/{record}'),
            'edit' => Pages\EditContrato::route('/{record}/edit'),
        ];
    }
}