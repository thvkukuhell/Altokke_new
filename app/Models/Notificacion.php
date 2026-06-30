<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'leida',
        'fecha_notificacion',
    ];

    protected function casts(): array 
    {
        return [
            'leida'                => 'boolean',
            'fecha_notificacion'   => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}