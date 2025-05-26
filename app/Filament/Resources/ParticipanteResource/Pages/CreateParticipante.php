<?php

namespace App\Filament\Resources\ParticipanteResource\Pages;

use App\Filament\Resources\ParticipanteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CreateParticipante extends CreateRecord
{
    protected static string $resource = ParticipanteResource::class;
    protected function afterCreate(): void
    {
        DB::beginTransaction();

        try {
            DB::commit();

            $this->sendSuccessNotification();
        } catch (QueryException $e) {
            DB::rollBack();

            Log::error('Error al crear el participante: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            $this->sendErrorNotification();
        }
    }
    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Participante creado')
            ->icon('heroicon-o-check-circle')
            ->body('El participante ha sido creado exitosamente')
            ->success()
            ->send();
    }
    private function sendErrorNotification(): void
    {
        Notification::make()
            ->title('Error al crear el participante')
            ->icon('heroicon-o-x-circle')
            ->body('Ocurrió un error al crear el participante, vuelve a intentarlo. Si el problema persiste, contacta al administrador.')
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
                    ->body('Ha ocurrido un error al crear el participante. Contacta al administrador.')
                    ->danger()
                    ->send()
            );
        }
    }
}
