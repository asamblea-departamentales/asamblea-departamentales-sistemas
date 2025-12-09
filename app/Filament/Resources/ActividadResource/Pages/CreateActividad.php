<?php

// Declaración del namespace para la página de creación de actividades

namespace App\Filament\Resources\ActividadResource\Pages;

// Importación del recurso ActividadResource
use App\Filament\Resources\ActividadResource;
// Importación de la clase base para páginas de creación de registros
use Filament\Actions;
// Importación de acciones de Filament
use Filament\Forms;
// Importación de componentes de formularios
use Filament\Forms\Form;
// Importación de la clase Form
use Filament\Notifications\Notification;
// Importación del sistema de notificaciones
use Filament\Resources\Pages\CreateRecord;
use TomatoPHP\FilamentMediaManager\Models\Folder;
use Illuminate\Support\Facades\Storage;
use TomatoPHP\FilamentMediaManager\Models\Media;
use Illuminate\Database\Eloquent\Model; // ← Asegúrate de tener este use arriba del archivo
use App\Models\Actividad;

// Declaración de la clase CreateActividad que extiende de CreateRecord
class CreateActividad extends CreateRecord
{
    // Propiedad protegida que asocia esta página con el recurso ActividadResource
    protected static string $resource = ActividadResource::class;

    // Propiedad que desactiva la opción de "crear otro" después de crear un registro
    protected static bool $canCreateAnother = false;

    // Método que define la estructura del formulario para la página de creación
    public function form(Form $form): Form
    {
        // Retorna el formulario configurado
        return $form
            // Define el esquema del formulario
            ->schema([
                // Crea un componente Wizard (asistente paso a paso)
                Forms\Components\Wizard::make([
                    // Primer paso del wizard: Información General
                    Forms\Components\Wizard\Step::make('Información General')
                        // Descripción que aparece bajo el título del paso
                        ->description('Datos básicos de la actividad')
                        // Ícono que representa este paso
                        ->icon('heroicon-o-information-circle')
                        // Esquema de campos para este paso
                        ->schema([
                            // Campo select para el usuario
                            Forms\Components\Select::make('user_id')
                                // Etiqueta del campo
                                ->label('Usuario')
                                // Define la relación y el campo a mostrar
                                ->relationship('user', 'firstname')
                                // Campo obligatorio
                                ->required()
                                // Permite búsqueda
                                ->searchable()
                                // Precarga las opciones
                                ->preload()
                                // Valor por defecto es el usuario autenticado
                                ->default(auth()->id())
                                // Campo deshabilitado (no editable)
                                ->disabled(),
                            // Campo oculto para la departamental (se asigna automáticamente)
                            Forms\Components\Hidden::make('departamental_id')
                                ->default(fn () => auth()->user()->departamental_id),

                            // Campo para seleccionar la fecha de la actividad
                            Forms\Components\DatePicker::make('fecha')
                                // Etiqueta del campo
                                ->label('Fecha de la Actividad')
                                // Campo obligatorio
                                ->required()
                                // Valor por defecto es la fecha actual
                                ->default(now())
                                // No usar el selector nativo del navegador
                                ->native(false)
                                // Formato de visualización
                                ->displayFormat('d/m/Y'),
                            // Campo de texto para la oficina departamental
                            Forms\Components\TextInput::make('departamental_display')
                            // Etiqueta del campo
                                ->label('Oficina Departamental')
                            // Campo solo lectura
                                ->default(fn () => auth()->user()->departamental->nombre ?? 'Sin departamental')
                                ->disabled()
                                ->dehydrated(false), // No se guarda en la base de datos
                            // Campo de texto para el programa
                            Forms\Components\Select::make('programa')
                                // Etiqueta del campo
                                ->label('Programa')
                                // Campo obligatorio
                                ->required()
                                // Opciones de Programas
                                ->options([
                                    'Programa de Educacion Civica'=> 'Programa de Educacion Civica',
                                    'Programa de Participacion Ciudadana'=> 'Programa de Participacion Ciudadana',
                                    'Programa de Atencion Ciudadana'=> 'Programa de Atencion Ciudadanda',
                                    'Otro'=> 'Otros'
                                ])
                                ->placeholder('Seleccione un programa')
                                ->searchable()
                                ->native(false),
                        
                            // Campo select para el estado de la actividad
                            Forms\Components\Select::make('estado')
                                // Etiqueta del campo
                                ->label('Estado de la Actividad')
                                // Opciones disponibles
                                ->options([
                                    'Pendiente' => 'Pendiente',
                                    'En Progreso' => 'En Progreso',
                                    'Completada' => 'Completada',
                                    'Cancelada' => 'Cancelada',
                                ])
                                // Campo obligatorio
                                ->required()
                                // Valor por defecto
                                ->default('Pendiente'),
                        ])
                        // Este paso tendrá 2 columnas
                        ->columns(2),
                    // Segundo paso del wizard: Detalles de la Actividad
                    Forms\Components\Wizard\Step::make('Detalles de la Actividad')
                    // Descripción del paso
                        ->description('Descripción detallada de la macroactividad')
                    // Ícono del paso
                        ->icon('heroicon-o-document-text')
                    // Esquema de campos para este paso
                        ->schema([
                            // Campo de área de texto para la macroactividad
                            Forms\Components\Textarea::make('macroactividad')
                                ->label('Macroactividad')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull()
                                ->placeholder('Describe detalladamente la actividad a realizar...')
                                ->helperText('Proporciona una descripción completa de la actividad, objetivos y alcance.'),

                            // Campo de texto para el lugar de la actividad
                            Forms\Components\TextInput::make('lugar')
                                ->label('Lugar')
                                ->required()
                                ->placeholder('Ingresa el lugar donde se realizará la actividad')
                                ->helperText('Indica el lugar físico o virtual de la actividad.')
                                ->columnSpanFull(),
                        ]),

                    // Tercer paso del wizard: Programación y Fechas
Forms\Components\Wizard\Step::make('Programación y Fechas')
->description('Fechas importantes y recordatorios')
->icon('heroicon-o-calendar-days')
->schema([
    Forms\Components\DateTimePicker::make('star_date')
        ->label('Fecha de Inicio')
        ->required()
        ->format('Y-m-d H:i:s') // <- CORREGIDO (era 'd/m/Y H:i')
        ->displayFormat('d/m/Y H:i')
        ->native(false)
        ->seconds(false) // <- AGREGADO
        ->default(now())
        ->helperText('Fecha y hora de inicio de la actividad'),
        
    Forms\Components\DateTimePicker::make('due_date')
        ->label('Fecha de Vencimiento')
        ->required()
        ->format('Y-m-d H:i:s')
        ->displayFormat('d/m/Y H:i')
        ->native(false)
        ->seconds(false) // <- AGREGADO
        ->default(now()->addDays(7))
        ->after('star_date')
        ->helperText('Fecha límite para completar la actividad'),
        
    Forms\Components\DateTimePicker::make('reminder_at')
        ->label('Recordatorio')
        ->format('Y-m-d H:i:s')
        ->displayFormat('d/m/Y H:i')
        ->native(false)
        ->seconds(false) // <- AGREGADO
        ->helperText('Opcional: Fecha y hora del recordatorio')
        ->before('due_date'),
])
->columns(3),
                    // Cuarto paso del wizard: Documentos y Atestados
                    Forms\Components\Wizard\Step::make('Documentos y Atestados')
                    ->description('Adjuntar archivos relacionados')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        \Filament\Forms\Components\SpatieMediaLibraryFileUpload::make('atestados')
                            ->label('Adjuntar Atestados')
                            ->multiple()
                            ->reorderable()
                            ->enableOpen()
                            ->enableDownload()
                            ->maxFiles(10)
                            ->maxSize(10240) // 10MB
                            ->disk('public')
                            ->directory('actividades')
                            ->preserveFilenames()
                            ->columnSpanFull()
                            ->helperText('Los archivos aparecerán automáticamente en la carpeta privada de tu departamental en el Media Manager.')
                            ->hint('Solo tu equipo puede verlos')
                            ->hintIcon('heroicon-o-lock-closed'),
                    ]),
                ])
                // Configuración del botón de submit del wizard
                    ->submitAction(
                        // Crea una acción personalizada para el submit
                        Forms\Components\Actions\Action::make('submit')
                            // Etiqueta del botón
                            ->label('Crear Actividad')
                            // Color del botón
                            ->color('primary')
                            // Acción que ejecuta al hacer submit
                            ->submit('create')
                    )
                // Permite saltar pasos (opcional)
                    ->skippable()
                // Mantiene el paso actual en la URL
                    ->persistStepInQueryString()
                // Inicia en el paso 1
                    ->startOnStep(1)
                // El wizard ocupa todo el ancho disponible
                    ->columnSpanFull(),
            ]);
    }

    // Método que se ejecuta antes de crear el registro para modificar los datos
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Validación: verifica si la fecha de vencimiento está en el pasado
        if (isset($data['due_date']) && $data['due_date'] < now()) {
            // Crea y envía una notificación de error
            Notification::make()
                ->title('Error de Validación')
                ->body('La fecha de vencimiento no puede ser en el pasado.')
                ->danger()
                ->send();
            // Detiene el proceso de creación
            $this->halt();
        }
        // Asigna el ID del usuario autenticado
        $data['user_id'] = auth()->id();

        // Retorna los datos modificados
        return $data;
    }

    // Método que define la notificación que se muestra después de crear el registro
    protected function getCreatedNotification(): ?Notification
    {
        // Retorna una notificación personalizada de éxito
        return Notification::make()
            ->title('¡Actividad creada exitosamente!')
            ->body('La actividad ha sido creada y está lista para ser gestionada.')
            ->success()
            ->duration(5000);  // Duración en milisegundos
    }

    // Método que define la URL de redirección después de crear el registro
    protected function getRedirectUrl(): string
    {
        // Redirige al índice (listado) del recurso
        return $this->getResource()::getUrl('index');
    }

    // Método que define el título de la página
    public function getTitle(): string
    {
        // Retorna el título de la página
        return 'Crear Nueva Actividad';
    }

    // Método que define el subtítulo de la página
    public function getSubheading(): ?string
    {
        // Retorna el subtítulo explicativo
        return 'Completa el wizard paso a paso para crear una nueva actividad con todas sus configuraciones.';
    }

    // Método que define las acciones disponibles en el encabezado de la página
    protected function getHeaderActions(): array
    {
        // Retorna un array de acciones
        return [
            // Acción de cancelar
            Actions\Action::make('cancel')
                // Etiqueta del botón
                ->label('Cancelar')
                // Color del botón
                ->color('gray')
                // URL a la que redirige (índice del recurso)
                ->url($this->getResource()::getUrl('index'))
                // Ícono del botón
                ->icon('heroicon-o-x-mark'),
        ];
    }


    protected function handleRecordCreation(array $data): Model
    {
        $record = new Actividad();
        $record->fill($data);
        $record->save();
    
        if (!empty($data['atestados'])) {
            foreach ($data['atestados'] as $file) {
                $record->addMedia($file)
                    ->preservingOriginal()
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('atestados', 'public');
            }
        }
    
        return $record;
    }
}
    // Cierre de la clase CreateActividad

