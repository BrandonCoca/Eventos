<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Aula;

class EventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->sentence(3),
            'tipo' => $this->faker->randomElement(['taller', 'congreso', 'conferencia', 'seminario']), // Valores válidos
            'fechainicio' => $this->faker->dateTimeBetween('now', '+1 month'),
            'fechafin' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'descripcion' => $this->faker->paragraph(),
            'aula_id' => Aula::factory(),
        ];
    }
}