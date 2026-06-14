<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table      = 'vehiculos';
    protected $primaryKey = 'id_vehiculo';

    protected $fillable = [
        'id_conductor',
        'placa',
        'marca',
        'modelo',
        'color',
        'anio',
        'numero_soat',
        'verificado_placa',
        'fecha_verificacion_placa',
    ];

    protected function casts(): array
    {
        return [
            'anio'                       => 'integer',
            'verificado_placa'           => 'boolean',
            'fecha_verificacion_placa'   => 'datetime',
        ];
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor', 'id_conductor');
    }
}
