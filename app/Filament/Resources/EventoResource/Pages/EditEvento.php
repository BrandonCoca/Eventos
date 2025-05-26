<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Doctrine\DBAL\Query\QueryException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditEvento extends EditRecord
{
    protected static string $resource = EventoResource::class;

    protected function afterSave(): void
    {
        DB::beginTransaction();

        try {

            DB::commit();

            $this->sendSuccessNotification();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Error al editar el evento: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            $this->sendErrorNotification();
        }
    }

    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Evento editado')
            ->icon('heroicon-o-check-circle')
            ->body('El evento ha sido editado exitosamente')
            ->success()
            ->send();
    }
    private function sendErrorNotification(): void
    {
        Notification::make()
            ->title('Error al editar el evento')
            ->icon('heroicon-o-x-circle')
            ->body('Ocurrió un error al editar el evento, vuelve a intentarlo. Si el problema persiste, contacta al administrador.')
            ->danger()
            ->send();
    }
    protected function getSavedNotification(): Notification|null
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        try {
            return $this->getResource()::getUrl('index');
        } catch (\Exception $e) {
            Log::error('Error al obtener URL de redirección: '. $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            return '/';
        }
    }

    protected function handleException($exception): void
    {
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $this->notify(
                Notification::make()
                    ->title('Error de validación')
                    ->body($exception->getMessage())
                    ->danger()
                    ->send()
            );
        } else {
            Log::error('Error no controlado: '. $exception->getMessage(), [
                'exception' => $exception,
                'user_id' => Auth::id(),
            ]);
            $this->notify(
                Notification::make()
                    ->title('Error')
                    ->body('Ha ocurrido un error al editar el evento. Contacta al administrador.')
                    ->danger()
                    ->send()
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
