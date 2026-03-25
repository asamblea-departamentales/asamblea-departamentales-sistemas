<?php

namespace App\Filament\Resources\ActividadResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;

class AtestadosRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Atestados';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('file')
                ->collection('atestados')
                ->multiple()
                ->disk('public'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('Preview')
                    ->getStateUsing(fn ($record) =>
                        str_contains($record->mime_type, 'image')
                            ? $record->getUrl()
                            : null
                    )
                    ->height(80),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Archivo')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('mime_type')
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->url(fn ($record) => $record->getUrl())
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}