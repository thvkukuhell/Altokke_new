<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table      = 'conductores';
    protected $primaryKey = 'id_conductor';
    public    $incrementing = false;

    protected $fillable = [
        'id_conductor',
        'licencia_numero',
        'estado_conductor',
        'saldo_disponible',
        'calificacion_promedio',
        'total_viajes',
        'verificado_dni',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class, 'id_conductor', 'id_usuario');
    }

    public function vehiculo()
    {
        return $this->hasOne(Vehiculo::class, 'id_conductor', 'id_conductor');
    }

    public function viajes()
    {
        return $this->hasMany(Viaje::class, 'id_conductor', 'id_conductor');
    }

    public function recargas()
    {
        return $this->hasMany(RecargaSaldo::class, 'id_conductor', 'id_conductor');
    }

    public function comisiones()
    {
        return $this->hasMany(Comision::class, 'id_conductor', 'id_conductor');
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoVerificacion::class, 'id_conductor', 'id_conductor');
    }
}