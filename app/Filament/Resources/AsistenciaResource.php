<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsistenciaResource\Pages;
use App\Models\Asistencia;
use App\Models\Inscripcion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class AsistenciaResource extends Resource
{
    protected static ?string $model = Asistencia::class;
    protected static ?string $modelLabel = 'Asistencia';
    protected static ?string $pluralModelLabel = 'Asistencias';
    protected static ?string $navigationGroup = 'Manejo del evento';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $activeNavigationIcon = 'heroicon-s-clipboard-document-check';
    protected static ?int $navigationSort = 2;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Asistencia')
                    ->description('Información de la asistencia')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('evento_id')
    ->label('Evento')
    ->placeholder('Seleccione un evento')
    ->helperText('Primero seleccione el evento')
    ->options(function () {
        return \App\Models\Evento::pluck('nombre', 'id');
    })
    ->searchable()
    ->native(false)
    ->preload()
    ->prefixIcon('heroicon-o-academic-cap')
    ->required()
    ->live()
    ->afterStateUpdated(function (callable $set, $state) {
        // Limpiar la selección de participante cuando cambia el evento
        $set('inscripcion_id', null);
    })
    ->validationMessages([
        'required' => 'El evento es obligatorio.',
    ]),

Forms\Components\Select::make('inscripcion_id')
    ->label('Participante inscrito')
    ->placeholder('Seleccione un participante inscrito')
    ->helperText('Seleccione un participante inscrito en el evento')
    ->options(function (callable $get) {
        $eventoId = $get('evento_id');
        
        if (!$eventoId) {
            return [];
        }
        
        return \App\Models\Inscripcion::with('participante')
            ->where('estado', true)
            ->where('evento_id', $eventoId)
            ->get()
            ->mapWithKeys(function ($inscripcion) {
                return [$inscripcion->id => $inscripcion->participante->nombre ?? 'Sin nombre'];
            });
    })
    ->searchable()
    ->native(false)
    ->live() // Importante para que se actualice cuando cambia el evento
    ->prefixIcon('heroicon-o-user')
    ->required()
    ->disabled(fn (callable $get) => !$get('evento_id'))
    ->validationMessages([
        'required' => 'La inscripción es obligatoria.',
    ]),
                        Forms\Components\Toggle::make('presente')
                            ->label('Asistencia')
                            ->default(true)
                            ->required()
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('inscripcion.participante.nombre')
                    ->label('Participante inscrito')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('inscripcion.evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-academic-cap')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('presente')
                    ->label('Asistencia')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('activo')
                    ->label('Asistidos')
                    ->query(fn(Builder $query): Builder => $query->where('presente', true))
                    ->indicator('Asistidos'),
                Tables\Filters\Filter::make('inactivo')
                    ->label('No Asistidos')
                    ->query(fn(Builder $query): Builder => $query->where('presente', false))
                    ->indicator('No Asistidos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make()
                    ->label('Detalles')
                    ->modalHeading('Detalles de la Inscripción')
                    ->modalIcon('heroicon-o-information-circle')
                    ->modalIconColor('info'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Eliminar Inscripción')
                    ->modalDescription('¿Está seguro de que desea eliminar esta inscripción? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotificationTitle('Inscripción eliminada')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Inscripción eliminada!')
                            ->body('La inscripción ha sido eliminada correctamente.')
                    ),
                Tables\Actions\Action::make('asistir')
                    ->label('Marcar Asistencia')
                    ->action(function (Asistencia $record) {
                        $record->update(['presente' => true]);
                        Notification::make()
                            ->success()
                            ->title('Asistencia marcada')
                            ->body('La asistencia ha sido marcada.')
                            ->send();
                    })
                    ->visible(fn($record) => !$record->presente)
                    ->color('success')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check')
                    ->modalHeading('¿Estás seguro de marcar la asistencia?')
                    ->modalDescription('Esta acción no se puede deshacer. ¿Deseas proceder?')
                    ->modalSubmitActionLabel('Sí, marcar')
                    ->modalIcon('heroicon-o-check')
                    ->modalIconColor('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la asistencia')
                    ->description('Datos de la asistencia')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('inscripcion.participante.nombre')
                            ->label('Participante')
                            ->icon('heroicon-o-user')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('inscripcion.evento.nombre')
                            ->label('Evento')
                            ->icon('heroicon-o-academic-cap')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        IconEntry::make('presente')
                            ->label('Asistencia')
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
            'index' => Pages\ListAsistencias::route('/'),
            'create' => Pages\CreateAsistencia::route('/create'),
            'edit' => Pages\EditAsistencia::route('/{record}/edit'),
        ];
    }
}
