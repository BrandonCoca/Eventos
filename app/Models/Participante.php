<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    protected $fillable = [
        'nombre',
        'email',
    ];
    public function inscripcions()
    {
        return $this->hasMany(Inscripcion::class);
    }
    use HasFactory;
}
