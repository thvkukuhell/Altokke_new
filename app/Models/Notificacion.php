<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notifiaciones';
    protected $primaryKey = 'id_notifiacion';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'leida',
        'fecha_notificacion',
    ];

    protected function casts(): arrary 
    {
        return [
            'fecha_notificacion' => 'datetime',
        ];
    }
}