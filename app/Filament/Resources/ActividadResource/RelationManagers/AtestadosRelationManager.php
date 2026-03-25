<?php

namespace App\Filament\Resources\ActividadResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AtestadosRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Atestados';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(fn () => $this->getOwnerRecord()->getMedia('atestados')->toQuery())
            
            ->columns([
                Tables\Columns\ImageColumn::make('id')
                    ->label('Preview')
                    ->getStateUsing(fn (Media $record) => $record->getUrl())
                    ->circular(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->badge(),

                Tables\Columns\TextColumn::make('size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subido')
                    ->dateTime('d/m/Y H:i'),
            ])

            ->actions([
                Tables\Actions\Action::make('ver')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (Media $record) => $record->getUrl(), true),

                Tables\Actions\Action::make('descargar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (Media $record) => $record->getUrl(), true),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar'),
            ])

            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Subir archivo')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->required()
                            ->disk('public')
                    ])
                    ->action(function (array $data) {
                        $this->getOwnerRecord()
                            ->addMedia(storage_path('app/public/' . $data['file']))
                            ->toMediaCollection('atestados');
                    }),
            ]);
    }
}