<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    use HasFactory;
}
