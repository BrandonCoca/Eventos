<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $modelLabel = 'Usuario';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $navigationGroup = 'Manejo del Personal';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $activeNavigationIcon = 'heroicon-s-user-circle';
    protected static ?int $navigationSort = 1;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                ->description('Información del usuario')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->placeholder('Ingrese el nombre completo')
                            ->helperText('Solo se permiten letras')
                            ->prefixIcon('heroicon-o-user')
                            ->autofocus()
                            ->required()
                            ->maxLength(40)
                            ->minLength(3)
                            ->regex('/^[\pL\sáéíóúñÑ]+$/u')
                            // Eliminar espacios en blanco adyacentes en tiempo real
                            ->dehydrateStateUsing(function ($state) {
                                // Eliminar espacios en blanco adyacentes
                                return preg_replace('/\s+/', ' ', trim($state));
                            })
                            ->validationMessages([
                                'required' => 'El nombre es obligatorio.',
                                'regex' => 'El nombre solo puede contener letras y espacios.',
                                'minLength' => 'El nombre debe tener al menos 3 caracteres.',
                                'maxLength' => 'El nombre debe tener menos de 40 caracteres.',
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->placeholder('Ingrese el correo electrónico')
                            ->prefixIcon('heroicon-o-at-symbol')
                            ->email()
                            ->suffixIcon('heroicon-o-envelope')
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->required()
                            ->autocomplete()
                            ->helperText('Ingrese un correo electrónico válido')
                            ->validationMessages([
                                'required' => 'El correo electrónico es obligatorio.',
                                'unique' => 'Ya existe un usuario con este correo electrónico.',
                                'email' => 'El correo electrónico debe ser válido.',
                            ]),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->placeholder('Ingrese la contraseña')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->password()
                            ->revealable()
                            ->required()
                            ->autocomplete('new-password')
                            ->helperText('Ingrese una contraseña segura')
                            ->minLength(8)
                            ->maxLength(20)
                            ->validationMessages([
                                'required' => 'La contraseña es obligatoria.',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope')
                    ->wrap(),
                Tables\Columns\TextColumn::make('activo')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Habilitado' : 'Deshabilitado')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\Filter::make('activo')
                    ->label('Activo')
                    ->query(fn(Builder $query): Builder => $query->where('activo', true))
                    ->indicator('Activos'),

                Tables\Filters\Filter::make('inactivo')
                    ->label('Inactivo')
                    ->query(fn(Builder $query): Builder => $query->where('activo', false))
                    ->indicator('Inactivos'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detalles')
                    ->modalHeading('Detalles del Usuario')
                    ->modalIcon('heroicon-o-information-circle')
                    ->modalIconColor('info'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('inhabilitar')
                    ->label('Inhabilitar')
                    ->action(function (User $record) {
                        $record->update(['activo' => false]);
                        Notification::make()
                            ->success()
                            ->title('Inhabilitado')
                            ->body('El usuario ha sido inhabilitado.')
                            ->send();
                    })
                    ->visible(fn($record) => $record->activo)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-x-mark')
                    ->modalHeading('¿Estás seguro de inhabilitar el usuario?')
                    ->modalDescription('Esta acción no se puede deshacer. ¿Deseas proceder?')
                    ->modalSubmitActionLabel('Sí, inhabilitar'),
                Tables\Actions\Action::make('habilitar')
                    ->label('Habilitar')
                    ->action(function (User $record) {
                        $record->update(['activo' => true]);
                        Notification::make()
                            ->success()
                            ->title('Habilitado')
                            ->body('El usuario ha sido habilitado.')
                            ->send();
                    })
                    ->visible(fn($record) => !$record->activo)
                    ->color('success')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check')
                    ->modalHeading('¿Estás seguro de habilitar el usuario?')
                    ->modalDescription('Esta acción no se puede deshacer. ¿Deseas proceder?')
                    ->modalSubmitActionLabel('Sí, habilitar')
                    ->modalIcon('heroicon-o-check')
                    ->modalIconColor('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información del usuario')
                    ->description('Datos del usuario')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nombre')
                            ->icon('heroicon-o-user')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('email')
                            ->label('Correo Electrónico')
                            ->icon('heroicon-o-envelope')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        IconEntry::make('activo')
                            ->label('Estado')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->size('lg'),

                        TextEntry::make('created_at')
                            ->label('Fecha de Creación')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray'),

                        TextEntry::make('updated_at')
                            ->label('Última Actualización')
                            ->icon('heroicon-o-clock')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray')
                            ->since(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
