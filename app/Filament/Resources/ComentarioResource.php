<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComentarioResource\Pages;
use App\Models\Comentarios;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComentarioResource extends Resource
{
    protected static ?string $model = Comentarios::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Comentarios';

    protected static ?string $navigationGroup = 'Actividades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('actividad_id')
                    ->relationship('actividad', 'macroactividad')
                    ->label('Actividad')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'firstname')
                    ->label('Autor del comentario')
                    ->default(auth()->id())
                    ->required()
                    ->disabled(),

                Forms\Components\Textarea::make('contenido')
                    ->label('Contenido del comentario')
                    ->required()
                    ->rows(4)
                    ->maxLength(500),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.firstname')
                ->label('Usuario')
                ->searchable(),

            Tables\Columns\TextColumn::make('actividad.macroactividad')
                ->label('Actividad')
                ->searchable()
                ->limit(50),

            Tables\Columns\TextColumn::make('contenido')
                ->label('Comentario')
                ->limit(80)
                ->tooltip(fn ($state) => $state),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Fecha de creación')
                ->dateTime('d/m/Y H:i'),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('actividad_id')->relationship('actividad', 'macroactividad'),
                Tables\Filters\SelectFilter::make('user')->relationship('user', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListComentarios::route('/'),
            'create' => Pages\CreateComentario::route('/create'),
            'edit' => Pages\EditComentario::route('/{record}/edit'),
        ];
    }
}
