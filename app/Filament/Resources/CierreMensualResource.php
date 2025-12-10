<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CierreMensualResource\Pages;
use App\Filament\Resources\CierreMensualResource\RelationManagers;
use App\Models\CierreMensual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CierreMensualResource extends Resource
{
    protected static ?string $model = CierreMensual::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Reportes y Cierres';

    protected static ?string $navigationLabel = 'Cierres Mensuales';

    protected static ?string $modelLabel = 'Cierre Mensual';
    protected static ?string $pluralModelLabel = 'Cierres Mensuales';


    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'cierre-mensuales';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Cierre')
                    ->schema([
                        Forms\Components\Select::make('departamental_id')
                            ->relationship('departamental', 'nombre')
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\Select::make('mes')
                            ->options([
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                                4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                                7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                                10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                            ])
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        
                        Forms\Components\TextInput::make('año')
                            ->numeric()
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Generado por')
                            ->disabled()
                            ->dehydrated(false),
                         
                        Forms\Components\DateTimePicker::make('fecha_cierre')
                            ->label('Fecha de Cierre')
                            ->disabled()
                            ->displayFormat('d/m/Y H:i')
                            ->dehydrated(false),    
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Métricas')
                    ->schema([
                        Forms\Components\TextInput::make('actividades_proyectadas')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('actividades_ejecutadas')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('actividades_pendientes')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('actividades_canceladas')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('porcentaje_cumplimiento')
                            ->suffix('%')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Estado y Observaciones')
                    ->schema([
                        Forms\Components\Select::make('estado')
                            ->options([
                                'generado' => 'Generado',
                                'aprobado' => 'Aprobado',
                                'reabierto' => 'Reabierto',
                            ])
                            ->required(),
                        
                        Forms\Components\Textarea::make('observaciones')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('departamental.nombre')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('mes')
                    ->formatStateUsing(fn ($state) => [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
                        4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
                        10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                    ][$state])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('año')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('porcentaje_cumplimiento')
                    ->suffix('%')
                    ->color(fn ($state) => $state >= 80 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'primary' => 'generado',
                        'success' => 'aprobado',
                        'warning' => 'reabierto',
                    ]),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Generado por')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('fecha_cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departamental_id')
                    ->relationship('departamental', 'nombre'),
                
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'generado' => 'Generado',
                        'aprobado' => 'Aprobado',
                        'reabierto' => 'Reabierto',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->url(fn ($record) => route('cierre.pdf', $record)) // 👈 pasa el modelo, no solo el ID
                ->openUrlInNewTab()
                ->visible(fn ($record) => filled($record->pdf_path)),
            

                Tables\Actions\Action::make('descargar_consolidado')
    ->label('Descargar Consolidado')
    ->icon('heroicon-o-document-arrow-down')
    ->url(fn ($record) => asset("storage/" . $record->pdf_path))    
    ->openUrlInNewTab()
    ->visible(fn () => auth()->user()->hasRole('gol')),

            
                // ✅ NUEVA ACCIÓN: APROBAR CIERRE
    Tables\Actions\Action::make('aprobar')
    ->label('Aprobar')
    ->color('success')
    ->icon('heroicon-o-check')
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->estado === 'generado')
    ->action(function ($record) {
        $record->update(['estado' => 'aprobado']);

        \Filament\Notifications\Notification::make()
            ->title('Cierre aprobado correctamente')
            ->success()
            ->send();
    }),    
                
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListCierreMensuals::route('/'),
            'create' => Pages\CreateCierreMensual::route('/create'),
            'edit' => Pages\EditCierreMensual::route('/{record}/edit'),
            'view' => Pages\ViewCierreMensual::route('/{record}'),

        ];
    }
// En app/Filament/Resources/CierreMensualResource.php

public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    $user = auth()->user();

    // 1. Roles que deben ver TODOS los registros (Superiores / Centrales)
    if ($user->hasAnyRole(['super_admin', 'ti', 'gol', 'auditoria'])) { 
        return parent::getEloquentQuery();
    }

    // 2. Roles que deben ver solo los registros de su DEPARTAMENTAL (Coordinador y Asistente Técnico)
    if ($user->hasAnyRole(['coordinador', 'asistente_tecnico'])) {
        $userDepartamentalId = $user->departamental_id;

        // Si el usuario no tiene departamental asignada (seguridad adicional)
        if (is_null($userDepartamentalId)) {
            return parent::getEloquentQuery()->whereRaw('1 = 0'); 
        }

        // Filtramos por su departamental
        return parent::getEloquentQuery()
            ->where('departamental_id', $userDepartamentalId);
    }

    // 3. Para cualquier otro rol no autorizado, devolvemos un conjunto vacío
    return parent::getEloquentQuery()->whereRaw('1 = 0');
}
}
