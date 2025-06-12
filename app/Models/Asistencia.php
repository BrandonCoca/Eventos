<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asistencia extends Model
{
    protected $fillable = [
        'presente',
        'inscripcion_id',
    ];
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class);
    }
    use HasFactory;
}
