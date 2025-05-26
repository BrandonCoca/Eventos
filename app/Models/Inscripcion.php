<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    protected $fillable = [
        'registro_id',
        'evento_id',
        'participante_id',
        'estado',
        'fecha',
    ];
    public function registro()
    {
        return $this->belongsTo(Registro::class);
    }
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }
    public function asistencia()
    {
        return $this->hasMany(Asistencia::class);
    }
}
