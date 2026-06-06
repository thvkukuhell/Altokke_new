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
}