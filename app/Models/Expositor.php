<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expositor extends Model
{
    protected $fillable = [
        'nombre',
        'email',
        'especialidad',
        'evento_id',
    ];
    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
