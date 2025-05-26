<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InscripcionResource\Pages;
use App\Filament\Resources\InscripcionResource\RelationManagers;
use App\Models\Inscripcion;
use App\Models\Participante;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class InscripcionResource extends Resource
{
    protected static ?string $model = Inscripcion::class;
    protected static ?string $modelLabel = 'Inscripción';
    protected static ?string $pluralModelLabel = 'Inscripciones';
    protected static ?string $navigationGroup = 'Manejo del evento';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $activeNavigationIcon = 'heroicon-s-rectangle-group';
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
                        Forms\Components\Select::make('participante_id')
                            ->label('Participante')
                            ->relationship('participante', 'nombre', function ($query) {
                                return $query->orderBy('created_at', 'desc');
                            })
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->placeholder('Seleccione el participante')
                            ->helperText('Seleccione el participante')
                            ->prefixIcon('heroicon-o-user')
                            ->required(),
                        Forms\Components\Select::make('evento_id')
                            ->label('Evento')
                            ->relationship('evento', 'nombre', function ($query) {
                                return $query->orderBy('created_at', 'desc');
                            })
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->placeholder('Seleccione el evento')
                            ->helperText('Seleccione el evento')
                            ->prefixIcon('heroicon-o-academic-cap')
                            ->required(),
                        Forms\Components\Select::make('registro_id')
                            ->label('Registro')
                            ->relationship('registro', 'tipo', function ($query) {
                                return $query->orderBy('created_at', 'desc');
                            })
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->placeholder('Seleccione el registro')
                            ->helperText('Seleccione el registro')
                            ->prefixIcon('heroicon-o-document-chart-bar')
                            ->required(),
                        Forms\Components\Toggle::make('estado')
                            ->label('Estado')
                            ->default(true)
                            ->required()
                            ->columnSpan('full'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('participante.nombre')
                    ->label('Participante')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-user')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('evento.nombre')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-academic-cap')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('registro.tipo')
                    ->label('Registro')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-document-chart-bar')
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('activo')
                    ->label('Pagado')
                    ->query(fn(Builder $query): Builder => $query->where('estado', true))
                    ->indicator('Pagados'),
                Tables\Filters\Filter::make('inactivo')
                    ->label('No Pagado')
                    ->query(fn(Builder $query): Builder => $query->where('estado', false))
                    ->indicator('No Pagados'),
                Tables\Filters\SelectFilter::make('evento_id')
                    ->label('Filtrar por Evento')
                    ->relationship('evento', 'nombre')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todos los eventos')
                    ->indicator('Evento'),
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
                Tables\Actions\Action::make('pagar')
                    ->label('Pagar')
                    ->action(function (Inscripcion $record) {
                        $record->update(['estado' => true]);
                        Notification::make()
                            ->success()
                            ->title('Pagado')
                            ->body('La inscripción ha sido pagada.')
                            ->send();
                    })
                    ->visible(fn($record) => !$record->estado)
                    ->color('success')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check')
                    ->modalHeading('¿Estás seguro de pagar la inscripción?')
                    ->modalDescription('Esta acción no se puede deshacer. ¿Deseas proceder?')
                    ->modalSubmitActionLabel('Sí, pagar')
                    ->modalIcon('heroicon-o-check')
                    ->modalIconColor('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar Inscripciones')
                        ->modalDescription('¿Está seguro de que desea eliminar las inscripciones seleccionadas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotificationTitle('Inscripciones eliminadas')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Inscripciones eliminadas!')
                                ->body('Las inscripciones seleccionadas han sido eliminadas correctamente.')
                        )
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información de la inscripción')
                    ->description('Datos de la inscripción')
                    ->icon('heroicon-o-user-circle')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('participante.nombre')
                            ->label('Participante')
                            ->icon('heroicon-o-user')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('evento.nombre')
                            ->label('Evento')
                            ->icon('heroicon-o-academic-cap')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        TextEntry::make('registro.tipo')
                            ->label('Registro')
                            ->icon('heroicon-o-document-chart-bar')
                            ->weight('bold')
                            ->copyable()
                            ->color('primary'),

                        IconEntry::make('estado')
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
            'index' => Pages\ListInscripcions::route('/'),
            'create' => Pages\CreateInscripcion::route('/create'),
            'edit' => Pages\EditInscripcion::route('/{record}/edit'),
        ];
    }
}
