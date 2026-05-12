<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Viaje extends Model
{
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
            'fecha_solicitud' => 'datetime',
            'fecha_inicio'    => 'datetime',
            'fecha_fin'       => 'datetime',
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
}