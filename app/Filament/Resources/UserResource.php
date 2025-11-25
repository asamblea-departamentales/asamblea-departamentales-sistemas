<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Tables\Actions\ImpersonateTableAction;
use App\Models\User;
use App\Settings\MailSettings;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static int $globalSearchResultsLimit = 20;

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $pluralLabel = 'Usuarios';

    protected static ?string $label = 'Usuario'; // para singular

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $slug = 'usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->hiddenLabel()
                            ->avatar()
                            ->collection('avatars')
                            ->alignCenter()
                            ->columnSpanFull()
                            ->image()
                            ->imagePreviewHeight('80')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('256')
                            ->imageResizeTargetHeight('256')
                            ->maxSize(1024)
                            ->maxFiles(1)
                            ->helperText(fn () => new HtmlString('<div class="text-center text-gray-300">'.__('resource.user.avatar_helper').'</div>')),

                        Forms\Components\Actions::make([
                            Action::make('resend_verification')
                                ->label(__('resource.user.actions.resend_verification'))
                                ->color('info')
                                ->action(fn (MailSettings $settings, Model $record) => static::doResendEmailVerification($settings, $record))
                                ->hidden(fn ($record) => $record?->email_verified_at !== null),
                        ])
                            ->hiddenOn('create')
                            ->fullWidth(),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->revealable()
                                    ->required(),
                                    Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirmar Contraseña')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->same('password') // ← este sí funciona
                                    ->dehydrated(false), // ← NO se manda al modelo (importante)
                            ])
                            ->compact()
                            ->hidden(fn (string $operation): bool => $operation === 'edit'),

                        Forms\Components\Placeholder::make('user_info')
                            ->hiddenLabel()
                            ->content(function (?User $record): HtmlString {
                                if (! $record) {
                                    return new HtmlString('<span class="text-sm text-gray-500">Save to see user details! 😊</span>');
                                }
                                $name = trim(($record->firstname ?? '').' '.($record->lastname ?? ''));
                                $joined = $record->created_at?->format('M j, Y \a\t g:i A') ?? 'just now';
                                $updated = $record->updated_at?->diffForHumans() ?? 'never';
                                $updatedExact = $record->updated_at?->format('M j, Y \a\t g:i A') ?? '';
                                if ($record->email_verified_at) {
                                    $verified = $record->email_verified_at->format('M j, Y \a\t g:i A');
                                    $sentence = "<span class='font-semibold'>$name</span> joined $joined and has been <span class='font-semibold' title='Verified on $verified'>verified</span> since then. Last updated <span class='font-semibold' title='Updated on $updatedExact'>$updated</span>.";
                                } else {
                                    $sentence = "<span class='font-semibold'>$name</span> joined $joined and hasn't verified their email yet. Last updated <span class='font-semibold' title='Updated on $updatedExact'>$updated</span>.";
                                }

                                return new HtmlString($sentence);
                            })
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpan(1),

                Forms\Components\Tabs::make()
                    ->schema([
                        Forms\Components\Tabs\Tab::make('Detalles del Usuario')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->rules(function ($record) {
                                        $userId = $record?->id;

                                        return $userId
                                            ? ['unique:users,username,'.$userId]
                                            : ['unique:users,username'];
                                    }),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->rules(function ($record) {
                                        $userId = $record?->id;

                                        return $userId
                                            ? ['unique:users,email,'.$userId]
                                            : ['unique:users,email'];
                                    })
                                    ->disabled(fn (string $operation) => $operation === 'edit')
                                    ->helperText(fn () => new HtmlString('<div class="text-gray-300">'.__('resource.user.email_edit_warning').'</div>')),

                                Forms\Components\TextInput::make('firstname')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('lastname')
                                    ->required()
                                    ->maxLength(255),

                                // === Departamental ===
                                Select::make('departamental_id')
                                    ->label('Departamental')
                                    ->relationship('departamental', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->default(fn () => Filament::auth()->user()?->departamental_id)
                                    ->disabled(function (): bool {
                                        $u = Filament::auth()->user();

                                        // TI, GOL o SuperAdmin pueden cambiar la oficina
                                        return ! ($u && ($u->hasAnyRole(['Administrador', 'GOL', 'SuperAdmin']) || $u->hasRole(config('filament-shield.super_admin.name'))));
                                    }),

                                // === Marcar email verificado (solo en crear) ===
                                Forms\Components\Toggle::make('mark_email_verified')
                                    ->label('Marcar email como verificado')
                                    ->default(true)
                                    ->visible(fn (string $operation) => $operation === 'create'),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Roles')
                            ->icon('fluentui-shield-task-48')
                            ->visible(function () {
                                $user = Filament::auth()->user();

                                return $user && (
                                    $user->hasRole('Administrador') ||
                                    $user->hasRole(config('filament-shield.super_admin.name'))
                                );
                            })
                            ->schema([
                                Select::make('roles')
                                    ->hiddenLabel()
                                    ->relationship('roles', 'name')
                                    ->getOptionLabelFromRecordUsing(function (Model $record) {
                                        return Str::headline($record->name);
                                    })
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->optionsLimit(5)
                                    ->columnSpanFull()
                                    ->helperText(__('resource.user.roles_tooltip')),
                            ]),
                    ])
                    ->columnSpan([
                        'sm' => 1,
                        'lg' => 2,
                    ]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')->label('Avatar')
                    ->collection('avatars')
                    ->wrap()
                    ->circular()
                    ->extraAttributes(['alt' => __('resource.user.avatar_alt')]),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->getStateUsing(fn (Model $record) => $record->firstname.' '.$record->lastname)
                    ->searchable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Nombre de Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500)
                    ->searchable(),

                // === Columna de Departamental ===
                Tables\Columns\TextColumn::make('departamental.nombre')
                    ->label('Departamental')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TagsColumn::make('roles.name')
                    ->label(__('resource.user.roles'))
                    ->separator(', ')
                    ->getStateUsing(fn ($record) => $record->roles->pluck('name')->toArray())
                    ->colors(['primary', 'success', 'warning', 'danger'])
                    ->badge(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('fluentui-checkmark-starburst-24')
                    ->falseIcon('fluentui-error-circle-24')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Model $record) => $record->email_verified_at ? __('resource.user.status.verified_tooltip') : __('resource.user.status.unverified_tooltip')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filtro por oficina
                SelectFilter::make('departamental_id')
                    ->label('Departamental')
                    ->relationship('departamental', 'nombre')
                    ->preload(),
                // Filtro por verificación
                TernaryFilter::make('email_verified')
                    ->label('Verificación')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('email_verified_at'),
                        false: fn ($q) => $q->whereNull('email_verified_at'),
                    ),
            ])
            ->modifyQueryUsing(function ($query) {
                /** @var \App\Models\User|null $user */
                $user = Filament::auth()->user();

                $isCentral = $user && ($user->hasAnyRole(['Administrador', 'GOL']) || $user->hasRole(config('filament-shield.super_admin.name')));

                if (! $isCentral && $user) {
                    $query->where('departamental_id', $user->departamental_id);
                }
            })

            ->actions([
                Tables\Actions\ActionGroup::make([
                    ImpersonateTableAction::make()->tooltip('Impersonate this user'),
                    Tables\Actions\EditAction::make()->tooltip('Edit user'),
                    Tables\Actions\DeleteAction::make()->tooltip('Delete user'),
                    Tables\Actions\Action::make('asignarRol')
                        ->label('Asignar Rol')
                        ->icon('heroicon-o-shield-check')
                        ->modalHeading('Asignar rol al usuario')
                        ->modalSubmitActionLabel('Asignar')
                        ->visible(fn () => Filament::auth()->user()?->hasRole('Administrador'))
                        ->form([
                            Select::make('roles')
                                ->label('Selecciona roles')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->required(),
                        ])
                        ->action(function (Model $record, array $data) {
                            $record->syncRoles($data['roles']);
                            Notification::make()
                                ->title('Roles actualizados')
                                ->success()
                                ->body('Los roles han sido asignados correctamente.')
                                ->send();
                        }),
                ])->label('Actions')->icon('heroicon-m-ellipsis-horizontal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Filament::auth()->user()?->hasRole('Administrador')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    {
        return $record->email;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email', 'firstname', 'lastname'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'name' => $record->firstname.' '.$record->lastname,
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('menu.nav_group.access');
    }

    public static function doResendEmailVerification($settings, $user): void
    {
        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;
            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        if ($settings->isMailSettingsConfigured()) {
            $notification = new VerifyEmail;
            $notification->url = Filament::getVerifyEmailUrl($user);

            $settings->loadMailSettingsToConfig();
            $user->notify($notification);

            Notification::make()
                ->title(__('resource.user.notifications.verify_sent.title'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('resource.user.notifications.verify_warning.title'))
                ->body(__('resource.user.notifications.verify_warning.description'))
                ->warning()
                ->send();
        }
    }
}
