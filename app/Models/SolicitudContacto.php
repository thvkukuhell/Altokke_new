<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SolicitudContacto extends Model
{
    protected $table = 'solicitudes_contacto';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'nombre',
        'correo',
        'asunto',
        'tipo_solicitud',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'id_usuario' => 'integer',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
