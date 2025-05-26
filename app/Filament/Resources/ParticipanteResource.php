<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipanteResource\Pages;
use App\Filament\Resources\ParticipanteResource\RelationManagers;
use App\Models\Participante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ParticipanteResource extends Resource
{
    protected static ?string $model = Participante::class;
    protected static ?string $modelLabel = 'Participante';
    protected static ?string $pluralModelLabel = 'Participantes';    
    protected static ?string $navigationGroup = 'Manejo de los implicados';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $activeNavigationIcon = 'heroicon-s-users';
    protected static ?int $navigationSort = 3;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Participante')
                ->description('Información del participante')
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nombre')
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
                        ->required()
                        ->helperText('Ingrese un correo electrónico válido')
                        ->validationMessages([
                            'required' => 'El correo electrónico es obligatorio.',
                            'email' => 'El correo electrónico debe ser válido.',
                        ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('Detalles')
                    ->modalHeading('Detalles del Participante')
                    ->modalIcon('heroicon-o-users')
                    ->modalIconColor('info'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Eliminar Participante')
                    ->modalDescription('¿Está seguro de que desea eliminar este participante? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotificationTitle('Participante eliminado')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Participante eliminado!')
                            ->body('El participante ha sido eliminado correctamente.')
                    )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar Participantes')
                        ->modalDescription('¿Está seguro de que desea eliminar los participantes seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotificationTitle('Participantes eliminados')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Participantes eliminados!')
                                ->body('Los participantes seleccionados han sido eliminados correctamente.')
                        )
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
                        TextEntry::make('nombre')
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
            'index' => Pages\ListParticipantes::route('/'),
            'create' => Pages\CreateParticipante::route('/create'),
            'edit' => Pages\EditParticipante::route('/{record}/edit'),
        ];
    }
}
