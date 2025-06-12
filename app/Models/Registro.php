<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class Registro extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo',
        'precio',
    ];
    public function inscripcions()
    {
        return $this->hasMany(Inscripcion::class);
    }
}
