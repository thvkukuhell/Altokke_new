<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasajero extends Model
{
    protected $table      = 'pasajeros';
    protected $primaryKey = 'id_pasajero';
    public    $incrementing = false; // la PK no es autoincremental

    protected $fillable = [
        'id_pasajero',
        'metodo_pago_preferido',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'id_pasajero', 'id_usuario');
    }

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'id_pasajero', 'id_pasajero');
    }
}