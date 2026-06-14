<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viaje extends Model
{
    use SoftDeletes;
    protected $table      = 'viajes';
    protected $primaryKey = 'id_viaje';

    protected $fillable = [
        'id_pasajero',
        'id_conductor',
        'origen_texto',
        'destino_texto',
        'lat_origen',
        'lng_origen',
        'lat_destino',
        'lng_destino',
        'tarifa_estimada',
        'tarifa_final',
        'tipo_servicio',
        'metodo_pago',
        'estado_viaje',
        'distancia_km',
        'tiempo_estimado_min',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'compartido',
    ];

    protected function casts(): array
    {
        return [
            'tarifa_estimada'     => 'decimal:2',
            'tarifa_final'        => 'decimal:2',
            'lat_origen'          => 'decimal:7',
            'lng_origen'          => 'decimal:7',
            'lat_destino'         => 'decimal:7',
            'lng_destino'         => 'decimal:7',
            'distancia_km'        => 'decimal:2',
            'compartido'          => 'boolean',
            'fecha_solicitud'     => 'datetime',
            'fecha_inicio'        => 'datetime',
            'fecha_fin'           => 'datetime',
        ];
    }

    // Relaciones
    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class, 'id_pasajero', 'id_pasajero');
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor', 'id_conductor');
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class, 'id_viaje', 'id_viaje');
    }

    public function comision() 
    {
        return $this->hasOne(Comision::class, 'id_viaje', 'id_viaje');
    }
}