<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Evento;
use App\Models\Participante;
use App\Models\Registro;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inscripcion>
 */
class InscripcionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'participante_id' => Participante::factory(),
            'registro_id' => Registro::factory(),
            'evento_id' => Evento::factory(),
            'estado' => fake()->boolean(),
            'fecha' => fake()->dateTimeBetween('-1 week', '+1 week'),
        ];
    }
    protected $casts = [
        'estado' => 'boolean',
    ];
}
