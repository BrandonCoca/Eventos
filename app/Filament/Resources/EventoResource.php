<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventoResource\Pages;
use App\Filament\Resources\EventoResource\RelationManagers;
use App\Models\Evento;
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

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;
    protected static ?string $modelLabel = 'Evento';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?string $navigationGroup = 'Manejo del evento';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $activeNavigationIcon = 'heroicon-s-academic-cap';
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
                            ->label('Nombre del evento')
                            ->placeholder('Ingrese el nombre del evento')
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
                        Forms\Components\Select::make('tipo')
                            ->label('Tipo de evento')
                            ->placeholder('Seleccione el tipo del evento')
                            ->helperText('Seleccione el tipo del evento')
                            ->prefixIcon('heroicon-o-document-chart-bar')
                            ->searchable()
                            ->options([
                                'taller' => 'Taller',
                                'congreso' => 'Congreso',
                                'conferencia' => 'Conferencia',
                                'seminario' => 'Seminario',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('fechainicio')
                            ->seconds(false)
                            ->native(false)
                            ->label('Fecha de inicio')
                            ->placeholder('Seleccione la fecha de inicio')
                            ->helperText('Seleccione la fecha de inicio del evento')
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->prefixIcon('heroicon-o-calendar')
                            ->timezone('America/La_Paz')
                            ->required(),
                        Forms\Components\DateTimePicker::make('fechafin')
                            ->seconds(false)
                            ->native(false)
                            ->label('Fecha de fin')
                            ->placeholder('Seleccione la fecha de fin')
                            ->helperText('Seleccione la fecha de fin del evento')
                            ->displayFormat('d/m/Y')
                            ->closeOnDateSelection()
                            ->prefixIcon('heroicon-o-calendar')
                            ->timezone('America/La_Paz')
                            ->required(),
                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción del evento')
                            ->placeholder('Ingrese una descripción del evento')
                            ->helperText('Escribe una descripción del evento, detalles adicionales, etc.')
                            ->autosize()
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'La descripción del evento es obligatoria.',
                                'maxLength' => 'La descripción del evento no puede tener más de 255 caracteres.',
                            ]),
                        Forms\Components\Select::make('aula_id')
                            ->label('Lugar')
                            ->relationship('aula', 'nombre', function ($query) {
                                return $query->orderBy('created_at', 'desc');
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nombre . ' - ' . $record->bloque)
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->placeholder('Seleccione el aula')
                            ->helperText('Seleccione el aula')
                            ->prefixIcon('heroicon-o-calendar')
                            ->required(),

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
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-document-chart-bar')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('fechainicio')
                    ->label('Fecha de inicio del evento')
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar-days')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fechafin')
                    ->label('Fecha de fin del evento')
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar-days')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('aula.bloque')
                    ->label('Lugar')
                    ->formatStateUsing(fn ($record) => $record->aula->nombre . ' - ' . $record->aula->bloque)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar')
                    ->weight('bold')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-calendar')
                    ->weight('bold')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('fecha')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fechainicio', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn(Builder $query, $date): Builder => $query->whereDate('fechainicio', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('Detalles')
                    ->modalHeading('Detalles del Evento')
                    ->modalIcon('heroicon-o-information-circle')
                    ->modalIconColor('info'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Eliminar Evento')
                    ->modalDescription('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotificationTitle('Evento eliminado')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Evento eliminado!')
                            ->body('El evento ha sido eliminado correctamente.')
                    )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar Eventos')
                        ->modalDescription('¿Está seguro de que desea eliminar los eventos seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotificationTitle('Eventos eliminados')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Eventos eliminados!')
                                ->body('Los eventos seleccionados han sido eliminados correctamente.')
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
                            ->icon('heroicon-o-envelope')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('descripcion')
                            ->label('Descripción')
                            ->color('primary'),

                        TextEntry::make('tipo')
                            ->label('Tipo')
                            ->weight('bold')
                            ->color('primary'),


                        TextEntry::make('fechainicio')
                            ->label('Fecha de inicio del evento')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray'),

                        TextEntry::make('fechafin')
                            ->label('Fecha de fin del evento')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('d/m/Y H:i')
                            ->color('gray'),

                        TextEntry::make('aula.bloque')
                            ->label('Aula')
                            ->formatStateUsing(fn ($record) => $record->aula->nombre . ' - ' . $record->aula->bloque)
                            ->icon('heroicon-o-map-pin')
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
            'index' => Pages\ListEventos::route('/'),
            'create' => Pages\CreateEvento::route('/create'),
            'edit' => Pages\EditEvento::route('/{record}/edit'),
        ];
    }
}
