<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $fillable = [
        'tipo',
        'precio',
    ];
    public function inscripcions()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
