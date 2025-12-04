<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActividadResource\Pages;
use App\Filament\Resources\ActividadResource\RelationManagers\ComentarioRelationManager;
use App\Models\Actividad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActividadResource extends Resource
{
    protected static ?string $model = Actividad::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $pluralLabel = 'Actividades';

    protected static ?string $label = 'Actividad'; // para el singular

    protected static ?string $navigationLabel = 'Actividades';

    protected static ?string $slug = 'actividades';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Actividad Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuario')
                            ->relationship('user', 'firstname')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->disabled()
                            ->dehydrated(false),

                            Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id())
                            ->required(),
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha de la Actividad')
                            ->required()
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->native(false),
                        Forms\Components\Hidden::make('departamental_id')
                            ->default(fn () => auth()->user()->departamental_id)
                            ->required()
                            ->rules(['exists:departamentales,id']),
                        Forms\Components\TextInput::make('departamental_display')
                            ->label('Oficina Departamental')
                            ->formatStateUsing(fn ($record) => $record?->departamental?->nombre ?? auth()->user()->departamental->nombre ?? 'Sin departamental')
                            ->disabled()
                            ->dehydrated(false),
                            Forms\Components\Select::make('programa')
                            ->label('Programa')
                            ->required()
                            ->options([
                                'Programa de Educacion Civica' => 'Programa de Educacion Civica',
                                'Programa de Participacion Ciudadana' => 'Programa de Participacion Ciudadana',
                                'Programa de Atencion Ciudadana' => 'Programa de Atencion Ciudadana',
                            ])
                            ->native(true) // ← Usa el select nativo del navegador
                            ->placeholder('Seleccione un programa')
                            ->columnSpanFull(),
                        
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Detalles de la Actividad')
                    ->schema([
                        Forms\Components\TextInput::make('lugar')
                            ->label('Lugar de la Actividad')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej: Auditorio Principal'),
                        Forms\Components\TextInput::make('asistentes_hombres')
                            ->label('Asistentes Hombres')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->inputMode('integer')
                            ->live()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('asistentes_mujeres')
                            ->label('Asistentes Mujeres')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->inputMode('integer')
                            ->live()
                            ->placeholder('0'),
                        Forms\Components\TextInput::make('asistencia_completa')
                            ->label('Asistencia Completa')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->inputMode('integer')
                            ->live()
                            ->placeholder('0')
                            ->rule(function (Forms\Get $get) {
                                return function (string $attribute, $value, $fail) use ($get) {
                                    $hombres = (int) $get('asistentes_hombres');
                                    $mujeres = (int) $get('asistentes_mujeres');
                            
                                    if ($value != $hombres + $mujeres) {
                                        $fail('La asistencia completa debe ser igual a la suma de asistentes hombres y mujeres.');
                                    }
                                };
                            }), //version top
                            //probando
                        Forms\Components\Textarea::make('macroactividad')
                            ->label('Macroactividad')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Describe la actividad...'),
                        Forms\Components\Select::make('estado')
                            ->label('Estado de la Actividad')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'En Progreso' => 'En Progreso',
                                'Completada' => 'Completada',
                                'Cancelada' => 'Cancelada',
                            ])
                            ->required()
                            ->default('Pendiente'),
                    ])
                    ->columns(2),
                    Forms\Components\Section::make('Fechas Importantes y Recordatorios')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Fecha de Inicio')
                            ->required()
                            ->format('Y-m-d H:i:s') // IMPORTANTE: esto fuerza Flatpickr
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->extraAttributes([
                                'x-on:click' => '$el.querySelector(\'input\').focus()',
                            ]),
                            
                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Fecha de Vencimiento')
                            ->required()
                            ->after('start_date')
                            ->format('Y-m-d H:i:s')
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->extraAttributes([
                                'x-on:click' => '$el.querySelector(\'input\').focus()',
                            ]),
                            
                        Forms\Components\DateTimePicker::make('reminder_at')
                            ->label('Recordatorio')
                            ->helperText('Opcional: Establece un recordatorio.')
                            ->before('due_date')
                            ->format('Y-m-d H:i:s')
                            ->displayFormat('d/m/Y H:i')
                            ->seconds(false)
                            ->extraAttributes([
                                'x-on:click' => '$el.querySelector(\'input\').focus()',
                            ]),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Atestados')
                    ->schema([
                        Forms\Components\FileUpload::make('atestados')
                            ->label('Adjuntar Atestados')
                            ->multiple()
                            ->directory('actividades')
                            ->acceptedFileTypes([
                                'image/*',
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'text/plain',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/zip',
                                'application/x-rar-compressed',
                                'video/*',
                                'audio/*',
                            ])
                            ->maxFiles(10)
                            ->maxSize(10240)
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->previewable()
                            ->columnSpanFull()
                            ->helperText('Máximo 10 archivos (imágenes, PDF, Word, Excel, ZIP, videos, audios).'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamental.nombre')
                    ->label('Oficina Departamental')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('programa')
                    ->label('Programa')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('macroactividad')
                    ->label('Macroactividad')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($state) => strlen($state) > 50 ? $state : null),
                    Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'primary' => 'Pendiente',
                        'warning' => 'En Progreso',
                        'success' => 'Completada',
                        'danger' => 'Cancelada',
                    ])
                    ->icon(fn (string $state): string => match ($state) {
                        'Pendiente' => 'heroicon-o-clock',
                        'En Progreso' => 'heroicon-o-arrow-path',
                        'Completada' => 'heroicon-o-lock-closed', // 🔒 Candado
                        'Cancelada' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-document-text',
                    }),
                Tables\Columns\TextColumn::make('lugar')
                    ->label('Lugar de la Actividad')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($state) => strlen($state) > 50 ? $state : null),
                Tables\Columns\TextColumn::make('asistencia_total')
                    ->label('Asistencia Total')
                    ->getStateUsing(fn ($record) => $record->asistentes_hombres + $record->asistentes_mujeres)
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha de la Actividad')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Fecha de Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Fecha de Vencimiento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date < now() && $record->estado !== 'Completada' ? 'danger' : null),
                Tables\Columns\IconColumn::make('reminder_at')
                    ->label('Recordatorio')
                    ->boolean()
                    ->trueIcon('heroicon-o-bell')
                    ->falseIcon('heroicon-o-bell-slash')
                    ->state(fn ($record) => ! is_null($record->reminder_at)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En Progreso' => 'En Progreso',
                        'Completada' => 'Completada',
                        'Cancelada' => 'Cancelada',
                    ]),
                    Tables\Filters\SelectFilter::make('departamental')
                    ->label('Departamental')
                    ->relationship('departamental', 'nombre')
                    ->searchable()
                    ->preload()
                    ->visible(function () {
                        $user = auth()->user();
                
                        // Permiso normal
                        $allowed = $user->can('view_any_actividad') && $user->hasRole(['Administrador', 'GOL']);
                
                        // Si la URL incluye un filtro por departamental, lo activamos
                        // Ejemplo recibido desde notificación:
                        // ?tableFilters[departamental][value]=2
                        $tableFilters = request()->query('tableFilters', []);
                        $hasDepartamentalFilter = isset($tableFilters['departamental']['value']);
                
                        return $allowed || $hasDepartamentalFilter;
                    }),
                
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'firstname',
                        modifyQueryUsing: function (Builder $query) {
                            $user = auth()->user();
                            if (! $user->hasRole(['Administrador', 'GOL'])) {
                                $query->where('departamental_id', $user->departamental_id);
                            }

                            return $query;
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole(['Administrador', 'GOL'])),
                Tables\Filters\Filter::make('asistencia_completa')
                    ->form([
                        Forms\Components\TextInput::make('min_asistencia')
                            ->label('Mínima Asistencia Completa')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Ej: 10'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['min_asistencia'],
                            fn (Builder $q, $value) => $q->where('asistencia_completa', '>=', $value)
                        );
                    }),
                Tables\Filters\Filter::make('vencimiento')
                    ->label('Próximas a Vencer')
                    ->query(fn (Builder $query): Builder => $query->where('due_date', '<=', now()->addDays(7))->where('estado', '!=', 'Completada'))
                    ->toggle(),
                Tables\Filters\Filter::make('fechas')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')
                            ->label('Fecha desde')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha hasta')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['fecha_desde'], fn (Builder $q, $date) => $q->whereDate('fecha', '>=', $date))
                            ->when($data['fecha_hasta'], fn (Builder $q, $date) => $q->whereDate('fecha', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) =>
                        auth()->user()->can('update_actividad')
                        && $record->estado !== 'Completada'
                    )
                    ->before(function (Model $record, Tables\Actions\EditAction $action) {
                        if ($record->estado === 'Completada') {
                            Notification::make()
                                ->title('Acción no permitida')
                                ->body('Esta actividad está completada y no puede ser editada.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
            
                        if (! auth()->user()->can('update_actividad')) {
                            Notification::make()
                                ->title('Permiso Denegado')
                                ->body('No tienes permiso para editar esta actividad.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
            
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) =>
                        auth()->user()->can('delete_actividad')
                        && $record->estado !== 'Completada'
                    )
                    ->before(function (Model $record, Tables\Actions\DeleteAction $action) {
                        if ($record->estado === 'Completada') {
                            Notification::make()
                                ->title('Acción no permitida')
                                ->body('Esta actividad está completada y no puede ser eliminada.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
            
                        if (! auth()->user()->can('delete_actividad')) {
                            Notification::make()
                                ->title('Permiso Denegado')
                                ->body('No tienes permiso para eliminar esta actividad.')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                    }),
                Tables\Actions\Action::make('mark_as_completed')
                    ->label('Marcar como Completada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => $record->estado !== 'Completada' && auth()->user()->can('update_actividad'))
                    ->form([
                        Forms\Components\TextInput::make('asistentes_hombres')
                            ->label('Asistentes Hombres')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->inputMode('integer')
                            ->live()
                            ->placeholder('0')
                            ->default(fn ($record) => $record->asistentes_hombres ?? 0),

                        Forms\Components\TextInput::make('asistentes_mujeres')
                            ->label('Asistentes Mujeres')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->inputMode('integer')
                            ->live()
                            ->placeholder('0')
                            ->default(fn ($record) => $record->asistentes_mujeres ?? 0),

                        Forms\Components\TextInput::make('asistencia_completa')
                            ->label('Asistencia Completa')
                            ->numeric()
                            ->disabled()
                            ->default(fn ($get) => ($get('asistentes_hombres') ?? 0) + ($get('asistentes_mujeres') ?? 0)),
                    ])
                    ->action(function (Model $record, array $data, Tables\Actions\Action $action) {
                        $record->update([
                            'estado' => 'Completada',
                            'asistentes_hombres' => $data['asistentes_hombres'],
                            'asistentes_mujeres' => $data['asistentes_mujeres'],
                            'asistencia_completa' => $data['asistentes_hombres'] + $data['asistentes_mujeres'],
                        ]);

                        Notification::make()
                            ->title('Actividad marcada como completada')
                            ->body('La actividad ha sido actualizada con éxito.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Registrar Asistencia y Completar Actividad')
                    ->modalDescription('Ingresa los datos de asistencia para marcar la actividad como completada.')
                    ->modalSubmitActionLabel('Confirmar')
                    ->modalWidth('lg'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('quick_add')
                    ->label('Añadir Rapido')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->visible(fn () => auth()->user()->can('create_actividad'))
                    ->modal()
                    ->modalHeading('Crear Nueva Actividad - Rápido')
                    ->modalDescription('Completa los campos básicos para crear una actividad rápidamente.')
                    ->modalWidth('lg')
                    ->slideOver()
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Usuario')
                                    ->relationship('user', 'firstname')
                                    ->required()
                                    ->default(auth()->id())
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),
                                Forms\Components\DatePicker::make('fecha')
                                    ->label('Fecha')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y')
                                    ->native(false),
                                Forms\Components\Hidden::make('departamental_id')
                                    ->default(fn () => auth()->user()->departamental_id)
                                    ->required()
                                    ->rules(['exists:departamentales,id']),
                                Forms\Components\TextInput::make('departamental_display')
                                    ->label('Oficina Departamental')
                                    ->default(fn () => auth()->user()->departamental->nombre ?? 'Sin departamental')
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('programa')
                                    ->label('Programa')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Programa de Desarrollo'),
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->label('Fecha de Inicio')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d/m/Y H:i')
                                    ->native(false),
                                Forms\Components\DateTimePicker::make('due_date')
                                    ->label('Fecha de Vencimiento')
                                    ->required()
                                    ->default(now()->addDays(7))
                                    ->after('start_date')
                                    ->displayFormat('d/m/Y H:i')
                                    ->native(false),
                                Forms\Components\Select::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'Pendiente' => 'Pendiente',
                                        'En Progreso' => 'En Progreso',
                                    ])
                                    ->default('Pendiente')
                                    ->required(),
                                Forms\Components\TextInput::make('lugar')
                                    ->label('Lugar de la Actividad')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej: Auditorio Principal'),
                                Forms\Components\TextInput::make('asistentes_hombres')
                                    ->label('Asistentes Hombres')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->inputMode('integer')
                                    ->live()
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('asistentes_mujeres')
                                    ->label('Asistentes Mujeres')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->inputMode('integer')
                                    ->live()
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('asistencia_completa')
                                    ->label('Asistencia Completa')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->inputMode('integer')
                                    ->live()
                                    ->placeholder('0')
                                    ->rules([
                                        'integer',
                                        function ($attribute, $value, $fail, $get) {
                                            $hombres = (int) $get('asistentes_hombres') ?? 0;
                                            $mujeres = (int) $get('asistentes_mujeres') ?? 0;
                                            if ($value !== ($hombres + $mujeres)) {
                                                $fail('La asistencia completa debe ser igual a la suma de asistentes hombres y mujeres.');
                                            }
                                        },
                                    ]),
                            ]),
                        Forms\Components\Textarea::make('macroactividad')
                            ->label('Macroactividad')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Describe brevemente la actividad...'),
                    ])
                    ->modalSubmitAction(
                        Tables\Actions\Action::make('submit')
                            ->label('Crear Rápido')
                            ->color('success')
                            ->action(function (array $data, Tables\Actions\Action $action) {
                                if ($data['due_date'] < now()) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body('La fecha de vencimiento no puede ser en el pasado.')
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                                if (empty($data['departamental_id'])) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body('El usuario debe estar asignado a una departamental.')
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                                $data['user_id'] = auth()->id();
                                try {
                                    $record = Actividad::create($data);
                                    Notification::make()
                                        ->title('¡Actividad creada exitosamente!')
                                        ->success()
                                        ->send();
                                } catch (\Illuminate\Validation\ValidationException $e) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                            })
                    )
                    ->modalCancelAction(
                        Tables\Actions\Action::make('cancel')
                            ->label('Cancelar')
                            ->color('gray')
                            ->action(fn () => null)
                    )
                    ->modalFooterActions([
                        Tables\Actions\Action::make('submit')
                            ->label('Crear Rápido')
                            ->color('success')
                            ->action(function (array $data, Tables\Actions\Action $action) {
                                if ($data['due_date'] < now()) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body('La fecha de vencimiento no puede ser en el pasado.')
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                                if (empty($data['departamental_id'])) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body('El usuario debe estar asignado a una departamental.')
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                                $data['user_id'] = auth()->id();
                                try {
                                    $record = Actividad::create($data);
                                    Notification::make()
                                        ->title('¡Actividad creada exitosamente!')
                                        ->success()
                                        ->send();
                                } catch (\Illuminate\Validation\ValidationException $e) {
                                    Notification::make()
                                        ->title('Error de Validación')
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                    $action->halt();
                                }
                            }),
                        Tables\Actions\Action::make('create_wizard')
                            ->label('Crear con Asistente')
                            ->icon('heroicon-o-sparkles')
                            ->color('primary')
                            ->url(fn () => static::getUrl('create'))
                            ->tooltip('Crear actividad paso a paso con todas las opciones'),
                        Tables\Actions\Action::make('cancel')
                            ->label('Cancelar')
                            ->color('gray')
                            ->action(fn () => null),
                    ]),
                Tables\Actions\CreateAction::make()
                    ->label('Crear con Asistente')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->url(fn () => auth()->user()->can('create_actividad')
                        ? static::getUrl('create')
                        : back()->with([
                            Notification::make()
                                ->title('Permiso denegado')
                                ->body('No puedes acceder al wizard de creación.')
                                ->danger()
                                ->send(),
                        ])
                    ),
            ])
            ->defaultSort('due_date', 'asc');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_any_actividad');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create_actividad');
    }

    public static function canEdit(Model $record): bool
    {
        //Verificar permiso y que no este completada
        return auth()->user()->can('update_actividad')
        && $record->estado !== 'Completada';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete_actividad')
        && $record->estado !== 'Completada';
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->can('delete_any_actividad');
    }

    public static function getRelations(): array
    {
        return [
            ComentarioRelationManager::class,
        ];
    }

    public static function getSlug(): string
    {
        return 'actividades';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActividades::route('/'),
            'create' => Pages\CreateActividad::route('/create'),
            'view' => Pages\ViewActividad::route('/{record}'),
            'edit' => Pages\EditActividad::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [];
    }
}
