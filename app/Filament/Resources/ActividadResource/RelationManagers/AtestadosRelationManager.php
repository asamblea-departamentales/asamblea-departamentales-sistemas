<?php

namespace App\Filament\Resources\ActividadResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class AtestadosRelationManager extends RelationManager
{
    protected static string $relationship = 'media'; // 🔥 importante

    protected static ?string $title = 'Atestados';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            SpatieMediaLibraryFileUpload::make('file')
                ->collection('atestados')
                ->required()
                ->multiple()
                ->disk('public'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label('Archivo'),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo'),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->url(fn ($record) => $record->getUrl())
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ]);
    }
}