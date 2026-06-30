<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecargaSaldo extends Model 
{
    protected $table = 'recarga_saldo';
    protected $primaryKey = 'id_recarga';

    protected $fillable = [
        'id_conductor',
        'monto',
        'metodo_recarga',
        'referencia',
        'comprobante_url',
        'estado_recarga',
        'fecha_solicitud',
        'fecha_aprobacion',
    ];

    protected function casts(): array 
    {
        return [
            'monto'              => 'decimal:2',
            'fecha_solicitud'    => 'datetime',
            'fecha_aprobacion'   => 'datetime',
        ];
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor', 'id_conductor');
    }
}