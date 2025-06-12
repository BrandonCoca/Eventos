<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Evento extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'fechainicio',
        'fechafin',
        'descripcion',
        'aula_id',
    ];
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
    public function inscripcion()
    {
        return $this->hasMany(Inscripcion::class);
    }
    public function expositor()
    {
        return $this->hasMany(Expositor::class);
    }
    use HasFactory;
}

