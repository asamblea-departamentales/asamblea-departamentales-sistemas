<?php

namespace App\Livewire;

use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Illuminate\Support\Facades\Hash;
use App\Models\Ticket;
use Carbon\Carbon;

use function Filament\Support\is_app_url;

class MyProfileExtended extends MyProfileComponent
{
    public ?array $data = [];
    public $user;

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $data = $this->getUser()->attributesToArray();
        $this->form->fill($data);
    }

    public function getUser(): Authenticatable&Model
    {
        $user = Filament::auth()->user();

        if (!$user instanceof Model) {
            throw new Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        return $user;
    }

    protected function isUserTI(): bool
    {
        $user = $this->getUser();
        return $user->hasAnyRole(['ti', 'super_admin']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Perfil')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->label('Avatar')
                            ->collection('avatars')
                            ->avatar()
                            ->required(),

                        Grid::make()->schema([
                            TextInput::make('username')
                                ->label('Usuario')
                                ->disabled()
                                ->required(),

                            TextInput::make('email')
                                ->label('Correo Electrónico')
                                ->disabled()
                                ->required(),
                        ]),

                        Grid::make()->schema([
                            TextInput::make('firstname')
                                ->label('Nombre')
                                ->required(),

                            TextInput::make('lastname')
                                ->label('Apellido')
                                ->required(),
                        ]),
                    ]),

                Section::make('Seguridad')
                    ->description('Cambia tu contraseña de acceso al sistema.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Contraseña Actual')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->revealable()
                            ->dehydrated(false),

                        Grid::make(2)->schema([
                            TextInput::make('password')
                                ->label('Nueva Contraseña')
                                ->password()
                                ->required()
                                ->minLength(8)
                                ->revealable()
                                ->dehydrated(fn ($state) => filled($state))
                                ->confirmed(),

                            TextInput::make('password_confirmation')
                                ->label('Confirmar Nueva Contraseña')
                                ->password()
                                ->required()
                                ->revealable()
                                ->dehydrated(false),
                        ]),
                    ])
                    ->visible(fn () => $this->isUserTI()),

                    Section::make('Contraseña')
                    ->description('Para cambiar su contraseña, debe solicitar autorización del departamento de TI.')
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('password_info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Haga clic en el botón de abajo para crear una solicitud de cambio de contraseña que será procesada por el departamento de TI.</p>
                                    <button 
                                        type="button"
                                        wire:click="solicitarCambioPassword"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        Solicitar Cambio de Contraseña
                                    </button>
                                </div>
                            ')),
                    ])
                    ->visible(fn () => !$this->isUserTI()),
            ])
            ->operation('edit')
            ->model($this->getUser())
            ->statePath('data');
    }

    public function submit()
    {
        try {
            $data = $this->form->getState();
            $this->handleRecordUpdate($this->getUser(), $data);

            Notification::make()
                ->title('Perfil actualizado')
                ->body('Los cambios se han guardado exitosamente.')
                ->success()
                ->send();

            $this->redirect('my-profile', navigate: FilamentView::hasSpaMode() && is_app_url('my-profile'));
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Error al actualizar')
                ->body('No se pudo guardar los cambios.')
                ->danger()
                ->send();
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($this->isUserTI() && filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['current_password'], $data['password_confirmation']);

        $record->update($data);

        return $record;
    }

    /**
    * Solicitar cambio de contraseña
   */
    public function solicitarCambioPassword(): void
    {
     $user = $this->getUser();

        try {
            // Verificar si ya existe solicitud pendiente
            $existingRequest = Ticket::where('tipo_ticket', 'SOLICITUD')
            ->where('motivo', 'like', '%Solicitud de cambio de contraseña por parte del usuario ' . $user->name . '%')
            ->where('estado_interno', 'PENDIENTE')
            ->exists();

            if ($existingRequest) {
                Notification::make()
                ->title('Solicitud Pendiente')
                ->warning()
                ->body('Ya tiene una solicitud de cambio de contraseña pendiente.')
                ->send();
                return;
            }

            Ticket::create([
            'tipo_ticket' => 'SOLICITUD',
            'motivo' => 'Solicitud de cambio de contraseña por parte del usuario ' . $user->name,
            'fecha_solicitud' => Carbon::now(),
            'estado_interno' => 'PENDIENTE',
            'oficina' => $user->oficina ?? 'No especificada',
            'observaciones' => 'El usuario ' . $user->email . ' ha solicitado un cambio de contraseña.'
        ]);

            Notification::make()
            ->title('Solicitud Enviada')
            ->success()
            ->body('Se ha creado el ticket para el cambio de contraseña.')
            ->send();

        } catch (\Exception $e) {
            \Log::error('Error al crear ticket: ' . $e->getMessage());

            Notification::make()
            ->title('Error')
            ->danger()
            ->body('No se pudo crear la solicitud.')
            ->send();
         }
    }


    public function render(): View
    {
        return view('livewire.my-profile-extended');
    }
}
