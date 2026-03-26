<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;

class ViewActividad extends ViewRecord
{
    protected static string $resource = ActividadResource::class;

    public function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([

            Section::make('Información General')
                ->schema([
                    TextEntry::make('user.firstname')->label('Usuario'),
                    TextEntry::make('departamental.nombre')->label('Departamental'),
                    TextEntry::make('programa')->label('Programa'),
                    TextEntry::make('estado')->label('Estado')->badge(),
                    TextEntry::make('fecha')->label('Fecha')->date('d/m/Y'),
                ])
                ->columns(2),

            Section::make('Detalles')
                ->schema([
                    TextEntry::make('lugar')->label('Lugar'),
                    TextEntry::make('macroactividad')
                        ->label('Macroactividad')
                        ->columnSpanFull(),
                ]),

            Section::make('Fechas')
                ->schema([
                    TextEntry::make('star_date')
                        ->label('Inicio')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('due_date')
                        ->label('Vencimiento')
                        ->dateTime('d/m/Y H:i'),

                    TextEntry::make('reminder_at')
                        ->label('Recordatorio')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('Sin recordatorio'),
                ])
                ->columns(3),

            Section::make('Atestados')
                ->schema([
                    View::make('filament.components.media-gallery')
                        ->viewData([
                            'media' => $this->record->getMedia('atestados'),
                        ])
                ])
                ->columnSpanFull(),

        ]);
}
}