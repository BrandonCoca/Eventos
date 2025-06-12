<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    use HasFactory;

}
