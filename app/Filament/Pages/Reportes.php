<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Evento;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Reportes extends Page
{
    protected static string $view = 'filament.pages.reportes';
    protected static ?string $modelLabel = 'Reportes';
    protected static ?string $pluralModelLabel = 'Reportes';
    
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $activeNavigationIcon = 'heroicon-s-chart-pie';
    
    protected static ?string $navigationGroup = 'Informes';
    protected static ?int $navigationSort = 4;

    public $reportData;

    public function mount(): void
    {
        // Obtener todos los eventos con sus inscripciones y asistencias
        $eventos = Evento::with(['inscripcion.asistencia'])
            ->get()
            ->map(function ($evento) {
                $totalInscritos = $evento->inscripcion->count();
                
                // Contar inscripciones que tienen al menos una asistencia con presente = true
                $totalAsistentes = $evento->inscripcion->filter(function ($inscripcion) {
                    return $inscripcion->asistencia->where('presente', true)->count() > 0;
                })->count();

                return [
                    'id' => $evento->id,
                    'nombre' => $evento->nombre,
                    'tipo' => $evento->tipo,
                    'fechainicio' => $evento->fechainicio,
                    'fechafin' => $evento->fechafin,
                    'total_inscritos' => $totalInscritos,
                    'total_asistentes' => $totalAsistentes,
                ];
            })
            ->filter(function ($evento) {
                // Solo incluir eventos que tienen al menos una inscripción
                return $evento['total_inscritos'] > 0;
            });

        // Agrupar por año usando Carbon
        $this->reportData = $eventos->groupBy(function ($evento) {
            return Carbon::parse($evento['fechainicio'])->year;
        })->sortKeysDesc(); // Ordenar años de forma descendente
    }

    protected function getViewData(): array
    {
        return [
            'reportData' => $this->reportData,
        ];
    }
    
    public static function getNavigationLabel(): string
    {
        return 'Reportes';
    }
}