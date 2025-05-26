<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';
    public static function getNavigationLabel(): string
    {
        return 'Inicio'; // Cambia el nombre en el menú lateral
    }

    public function getTitle(): string
    {
        return 'Control de participantes en eventos académicos de la FNI'; // Título de la página
    }
}
