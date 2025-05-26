<?php

namespace App\Filament\Resources\AulaResource\Pages;

use App\Filament\Resources\AulaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CreateAula extends CreateRecord
{
    protected static string $resource = AulaResource::class;
    protected function afterCreate(): void
    {
        DB::beginTransaction();

        try {
            DB::commit();

            $this->sendSuccessNotification();
        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('Error al crear el aula: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            $this->sendErrorNotification();
        }
    }
    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Aula creado')
            ->icon('heroicon-o-check-circle')
            ->body('El aula ha sido creado exitosamente')
            ->success()
            ->send();
    }
    private function sendErrorNotification(): void
    {
        Notification::make()
            ->title('Error al crear el aula')
            ->icon('heroicon-o-x-circle')
            ->body('Ocurrió un error al crear el aula, vuelve a intentarlo. Si el problema persiste, contacta al administrador.')
            ->danger()
            ->send();
    }

    protected function getCreatedNotification(): \Filament\Notifications\Notification|null
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
        if ($exception instanceof ValidationException) {
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
                    ->body('Ha ocurrido un error al crear el aula. Contacta al administrador.')
                    ->danger()
                    ->send()
            );
        }
    }
}
