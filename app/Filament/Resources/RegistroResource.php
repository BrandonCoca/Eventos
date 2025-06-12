<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistroResource\Pages;
use App\Filament\Resources\RegistroResource\RelationManagers;
use App\Models\Registro;
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

class RegistroResource extends Resource
{
    protected static ?string $model = Registro::class;
    protected static ?string $modelLabel = 'Registro';
    protected static ?string $pluralModelLabel = 'Registros';
    protected static ?string $navigationGroup = 'Manejo del evento';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $activeNavigationIcon = 'heroicon-s-document-text';
    protected static ?int $navigationSort = 2;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tipo')
                            ->label('Grado del participante')
                            ->placeholder('Seleccione el grado')
                            ->helperText('Seleccione el grado')
                            ->prefixIcon('heroicon-o-document-chart-bar')
                            ->searchable()
                            ->options([
                                'estudiante' => 'Estudiante',
                                'ingeniero' => 'Ingeniero',
                                'externo' => 'Externo',
                            ])
                            ->required()
                            ->validationMessages([
                                'required' => 'El grado es obligatorio.',
                            ]),
                Forms\Components\TextInput::make('precio')
                            ->label('Precio')
                            ->placeholder('Ingrese el precio')
                            ->helperText('Ingrese el precio')
                            ->prefixIcon('heroicon-o-document-chart-bar')
                            ->minValue(1)
                            ->numeric()
                            ->required()
                            ->validationMessages([
                                'required' => 'El precio es obligatorio.',
                                'minValue' => 'El precio debe ser mayor o igual a 1.',
                                'numeric' => 'El precio debe ser un número.',
                            ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-document-chart-bar')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Grado del participante')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->icon('heroicon-o-document-chart-bar')
                    ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Eliminar Registro')
                    ->modalDescription('¿Está seguro de que desea eliminar este registro? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->successNotificationTitle('Registro eliminado')
                    ->successNotification(
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('¡Registro eliminado!')
                            ->body('El registro ha sido eliminado correctamente.')
                    )
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Eliminar seleccionados')
                        ->modalHeading('Eliminar Registros')
                        ->modalDescription('¿Está seguro de que desea eliminar los registros seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->successNotificationTitle('Registros eliminados')
                        ->successNotification(
                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('¡Registros eliminados!')
                                ->body('Los registros seleccionados han sido eliminados correctamente.')
                        )
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
            'index' => Pages\ListRegistros::route('/'),
            'create' => Pages\CreateRegistro::route('/create'),
            'edit' => Pages\EditRegistro::route('/{record}/edit'),
        ];
    }
}
