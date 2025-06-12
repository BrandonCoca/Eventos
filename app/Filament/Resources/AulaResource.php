<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AulaResource\Pages;
use App\Filament\Resources\AulaResource\RelationManagers;
use App\Models\Aula;
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

class AulaResource extends Resource
{
    protected static ?string $model = Aula::class;
    protected static ?string $modelLabel = 'Sitio';
    protected static ?string $pluralModelLabel = 'Sitios';

    protected static ?string $navigationGroup = 'Manejo del evento';
    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-ripple';
    protected static ?string $activeNavigationIcon = 'heroicon-s-cursor-arrow-ripple';
    protected static ?int $navigationSort = 2;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Expositor')
                    ->description('Información del expositor')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del lugar')
                            ->placeholder('Ejemplo: Sistemas, Civil, etc.')
                            ->helperText('Solo se permiten letras')
                            ->prefixIcon('heroicon-o-user')
                            ->autofocus()
                            ->required()
                            ->maxLength(20)
                            ->minLength(5)
                            ->regex('/^[\pL\sáéíóúñÑ]+$/u')
                            // Eliminar espacios en blanco adyacentes en tiempo real
                            ->dehydrateStateUsing(function ($state) {
                                // Eliminar espacios en blanco adyacentes
                                return preg_replace('/\s+/', ' ', trim($state));
                            })
                            ->validationMessages([
                                'required' => 'El nombre es obligatorio.',
                                'regex' => 'El nombre solo puede contener letras y espacios.',
                                'minLength' => 'El nombre debe tener al menos 5 caracteres.',
                                'maxLength' => 'El nombre debe tener menos de 20 caracteres.',
                            ]),
                        Forms\Components\TextInput::make('bloque')
                            ->label('Aula')
                            ->placeholder('Ingrese el  aula')
                            ->prefixIcon('heroicon-o-briefcase')
                            ->required()
                            ->helperText('Ejemplo: SIS 5, SID 6, LSIB 2')
                            ->minLength(length: 3)
                            ->maxLength(10)
                            ->validationMessages([
                                'required' => 'La aula es obligatoria.',
                                'minLength' => 'La aula debe tener al menos 3 caracteres.',
                                'maxLength' => 'La aula debe tener menos de 10 caracteres.',
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Lugar')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('bloque')
                    ->label('Aula')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-briefcase')
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('Detalles')
                    ->modalHeading('Detalles del Aula')
                    ->modalIcon('heroicon-o-information-circle')
                    ->modalIconColor('info'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Eliminar Aula')
                    ->modalDescription('¿Está seguro de que desea eliminar esta aula? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotificationTitle('Aula eliminada')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Aula eliminada!')
                            ->body('El aula ha sido eliminada correctamente.')
                    )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar Aulas')
                        ->modalDescription('¿Está seguro de que desea eliminar los aulas seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotificationTitle('Aulas eliminados')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Aulas eliminados!')
                                ->body('Los aulas seleccionados han sido eliminados correctamente.')
                        )
                ]),
            ]);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información del aula')
                    ->description('Datos del aula')
                    ->icon('heroicon-o-cursor-arrow-ripple')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre')
                            ->icon('heroicon-o-user')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('bloque')
                            ->label('Bloque')
                            ->icon('heroicon-o-briefcase')
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
            'index' => Pages\ListAulas::route('/'),
            'create' => Pages\CreateAula::route('/create'),
            'edit' => Pages\EditAula::route('/{record}/edit'),
        ];
    }
}
