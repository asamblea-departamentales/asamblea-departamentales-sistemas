<?php

namespace App\Livewire;

use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Jeffgreco13\FilamentBreezy\Livewire\MyProfileComponent;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
use Illuminate\Support\Facades\Hash;

use function Filament\Support\is_app_url;

class MyProfileExtended extends MyProfileComponent
{
    /**
     * @var array<string, mixed> | null
     */
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

        if (! $user instanceof Model) {
            throw new Exception('The authenticated user object must be an Eloquent model to allow the profile page to update it.');
        }

        return $user;
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

            // Solo mostrar cambio de contraseña si tiene rol de TI
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
                ->visible(fn () => $this->getUser()->role === 'ti'), // Cambia esto según tu sistema de roles

            // Botón para solicitar cambio de contraseña (para usuarios que NO son TI)
            Section::make('Contraseña')
                ->description('Para cambiar su contraseña, debe solicitar autorización del departamento de TI.')
                ->schema([
                    ViewField::make('password_request')
                        ->view('livewire.request-password-change')
                        ->label(''),
                ])
                ->visible(fn () => $this->getUser()->role !== 'ti'),
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
                ->title('Profile updated')
                ->success()
                ->send();

            $this->redirect('my-profile', navigate: FilamentView::hasSpaMode() && is_app_url('my-profile'));
        } catch (\Throwable $th) {
            Notification::make()
                ->title('Failed to update.')
                ->danger()
                ->send();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
{
    // Si el usuario es TI y está cambiando la contraseña
    if ($record->role === 'ti' && filled($data['password'] ?? null)) {
        $data['password'] = Hash::make($data['password']);
    } else {
        // Remover campos de contraseña si no es TI
        unset($data['password']);
    }

    // Remover campos que no deben guardarse
    unset($data['current_password']);
    unset($data['password_confirmation']);

    $record->update($data);

    return $record;
}

    public function render(): View
    {
        return view('livewire.my-profile-extended');
    }
}
