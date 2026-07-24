<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogoResource\Pages;
use App\Models\Catalogo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CatalogoResource extends Resource
{
    protected static ?string $model = Catalogo::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $label = 'Catálogo';

    protected static ?string $pluralLabel = 'Catálogos';

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['ti', 'super_admin', 'asistente_tecnico']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('grupo')
                ->label('Grupo')
                ->options([
                    'programa' => 'Programas',
                    'rubro' => 'Rubros',
                    'tipo_insumo' => 'Tipos de Insumo',
                    'tipo_contrato' => 'Tipos de Contrato',
                    'tipo_ticket' => 'Tipos de Ticket',
                ])
                ->required()
                ->disabled(fn (?Catalogo $record) => $record !== null)
                ->native(true),

            Forms\Components\TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(255)
                ->dehydrated()
                ->rules(['alpha_dash'])
                ->helperText('Identificador único. Ej: PAPELERIA, SOPORTE_TECNICO'),

            Forms\Components\TextInput::make('label')
                ->label('Etiqueta')
                ->required()
                ->maxLength(255)
                ->helperText('Nombre que se muestra al usuario'),

            Forms\Components\Toggle::make('activo')
                ->label('Activo')
                ->default(true),

            Forms\Components\TextInput::make('orden')
                ->label('Orden')
                ->numeric()
                ->default(0)
                ->minValue(0)
                ->maxValue(999),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('grupo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'programa' => 'info',
                        'rubro' => 'success',
                        'tipo_insumo' => 'warning',
                        'tipo_contrato' => 'primary',
                        'tipo_ticket' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'programa' => 'Programas',
                        'rubro' => 'Rubros',
                        'tipo_insumo' => 'Tipos de Insumo',
                        'tipo_contrato' => 'Tipos de Contrato',
                        'tipo_ticket' => 'Tipos de Ticket',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('slug')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('label')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('activo')->boolean(),
                Tables\Columns\TextColumn::make('orden')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->label('Creado'),
            ])
            ->defaultSort('grupo')
            ->defaultSort('orden')
            ->reorderable('orden')
            ->filters([
                Tables\Filters\SelectFilter::make('grupo')
                    ->options([
                        'programa' => 'Programas',
                        'rubro' => 'Rubros',
                        'tipo_insumo' => 'Tipos de Insumo',
                        'tipo_contrato' => 'Tipos de Contrato',
                        'tipo_ticket' => 'Tipos de Ticket',
                    ]),
                Tables\Filters\TernaryFilter::make('activo')->label('Activos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogos::route('/'),
            'create' => Pages\CreateCatalogo::route('/create'),
            'edit' => Pages\EditCatalogo::route('/{record}/edit'),
        ];
    }
}
