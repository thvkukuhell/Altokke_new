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

    protected function casts(): array
    {
        return [
            'puntuacion'           => 'decimal:1',
            'fecha_calificacion'   => 'datetime',
        ];
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'id_viaje', 'id_viaje');
    }
}