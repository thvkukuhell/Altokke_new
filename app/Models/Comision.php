<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model 
{
    protected $table = 'comisiones';
    protected $primaryKey = 'id_comision';

    protected $fillable = [
        'id_viaje',
        'id_conductor',
        'monto_comision',
        'fecha_descuento',
    ];

    protected function casts(): array
    {
        return [
            'monto_comision'  => 'decimal:2',
            'fecha_descuento' => 'date',
        ];
    }

    public function viaje()
    {
        return $this->belongsTo(Viaje::class, 'id_viaje', 'id_viaje');
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor', 'id_conductor');
    }
}