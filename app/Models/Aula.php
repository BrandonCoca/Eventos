<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $fillable = [
        'nombre',
        'bloque',
    ];

    public function evento()
    {
        return $this->hasMany(Evento::class);
    }
}
