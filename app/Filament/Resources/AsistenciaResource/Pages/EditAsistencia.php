<?php

namespace App\Filament\Resources\AsistenciaResource\Pages;

use App\Filament\Resources\AsistenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Doctrine\DBAL\Query\QueryException;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EditAsistencia extends EditRecord
{
    protected static string $resource = AsistenciaResource::class;

    protected function afterSave(): void
    {
        DB::beginTransaction();

        try {

            DB::commit();

            $this->sendSuccessNotification();
        } catch (QueryException $e) {
            DB::rollBack();
            Log::error('Error al editar la asistencia: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
            ]);
            $this->sendErrorNotification();
        }
    }

    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->title('Asistencia editada')
            ->icon('heroicon-o-check-circle')
            ->body('La asistencia ha sido editada exitosamente')
            ->success()
            ->send();
    }
    private function sendErrorNotification(): void
    {
        Notification::make()
            ->title('Error al editar la asistencia')
            ->icon('heroicon-o-x-circle')
            ->body('Ocurrió un error al editar la asistencia, vuelve a intentarlo. Si el problema persiste, contacta al administrador.')
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
                    ->body('Ha ocurrido un error al editar la asistencia. Contacta al administrador.')
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
