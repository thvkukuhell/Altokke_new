<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionTarifa extends Model
{
    protected $table = 'configuracion_tarifas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tipo_servicio',
        'tarifa_base',
        'precio_por_km',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'tarifa_base'   => 'decimal:2',
            'precio_por_km' => 'decimal:2',
            'activo'        => 'boolean',
        ];
    }
}
