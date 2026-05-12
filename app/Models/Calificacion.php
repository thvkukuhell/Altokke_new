<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table      = 'calificaciones';
    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'id_viaje',
        'puntuacion',
        'comentario',
        'fecha_calificacion',
    ];

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'id_viaje', 'id_viaje');
    }
}