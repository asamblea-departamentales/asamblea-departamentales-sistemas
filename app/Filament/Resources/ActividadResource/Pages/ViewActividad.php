<?php

namespace App\Filament\Resources\ActividadResource\Pages;

use App\Filament\Resources\ActividadResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewActividad extends ViewRecord
{
    protected static string $resource = ActividadResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                // 🔹 INFO GENERAL
                Infolists\Components\Section::make('Información General')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.firstname')
                            ->label('Usuario'),

                        Infolists\Components\TextEntry::make('departamental.nombre')
                            ->label('Departamental'),

                        Infolists\Components\TextEntry::make('programa')
                            ->label('Programa'),

                        Infolists\Components\TextEntry::make('estado')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Pendiente' => 'primary',
                                'En Progreso' => 'warning',
                                'Completada' => 'success',
                                'Cancelada' => 'danger',
                            }),
                    ])
                    ->columns(2),

                // 🔹 DETALLES
                Infolists\Components\Section::make('Detalles')
                    ->schema([
                        Infolists\Components\TextEntry::make('macroactividad')
                            ->label('Macroactividad'),

                        Infolists\Components\TextEntry::make('lugar')
                            ->label('Lugar'),
                    ]),

                // 🔹 FECHAS
                Infolists\Components\Section::make('Fechas')
                    ->schema([
                        Infolists\Components\TextEntry::make('fecha')
                            ->label('Fecha')
                            ->date('d/m/Y'),

                        Infolists\Components\TextEntry::make('star_date')
                            ->label('Inicio')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('due_date')
                            ->label('Vencimiento')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('reminder_at')
                            ->label('Recordatorio')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('No definido'),
                    ])
                    ->columns(2),

                // 🔥 GALERÍA PRO
                Infolists\Components\Section::make('Atestados')
    ->schema([
        Infolists\Components\RepeatableEntry::make('atestados_urls')
            ->label('')
            ->schema([

                Infolists\Components\ImageEntry::make('url')
                    ->label('Vista previa')
                    ->height(120),

                Infolists\Components\TextEntry::make('name')
                    ->label('Nombre'),

                Infolists\Components\TextEntry::make('size')
                    ->label('Tamaño (KB)')
                    ->formatStateUsing(fn ($state) => round($state / 1024, 2)),

                Infolists\Components\TextEntry::make('url')
                    ->label('Acciones')
                    ->formatStateUsing(fn ($state) =>
                        "<a href='{$state}' target='_blank'>👁 Ver</a> | 
                         <a href='{$state}' download>⬇ Descargar</a>"
                    )
                    ->html(),

            ])
            ->columns(2),
    ]),
            ]);
    }
}