<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;

class TicketComentarioRelationManager extends RelationManager
{
    protected static string $relationship = 'comentarios';

    protected static ?string $recordTitleAttribute = 'contenido';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('contenido')
                ->label('Comentario')
                ->required()
                ->rows(3)
                ->maxLength(1000),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
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
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo Comentario')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['ticket_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id() || auth()->user()->hasAnyRole(['ti', 'super_admin', 'coordinador'])),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->user_id === auth()->id() || auth()->user()->hasAnyRole(['ti', 'super_admin', 'coordinador'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasAnyRole(['ti', 'super_admin', 'coordinador'])),
                ]),
            ]);
    }
}
