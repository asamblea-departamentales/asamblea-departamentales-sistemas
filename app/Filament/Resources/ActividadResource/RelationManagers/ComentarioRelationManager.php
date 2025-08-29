<?php

namespace App\Filament\Resources\ActividadResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class ComentarioRelationManager extends RelationManager
{
    protected static string $relationship = 'comentarios';

    protected static ?string $recordTitleAttribute = 'contenido';

    public function form(Forms\Form $form): Forms\Form // Quité 'static'
    {
        return $form->schema([
            Forms\Components\Textarea::make('contenido')
                ->label('Comentario')
                ->required()
                ->rows(3)
                ->maxLength(1000), // Opcional: límite de caracteres
        ]);
    }

    public function table(Tables\Table $table): Tables\Table // Quité 'static'
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contenido')
                    ->label('Comentario')
                    ->limit(80)
                    ->tooltip(fn ($state) => $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Comentarios más recientes primero
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Comentario')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()), // Solo el autor puede editar
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id()), // Solo el autor puede eliminar
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
