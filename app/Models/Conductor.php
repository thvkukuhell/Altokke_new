<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conductor extends Model
{
    use SoftDeletes;
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
        'fecha_verificacion_dni',
        'lat_actual',
        'lng_actual',
        'ubicacion_actualizada_en',
    ];

    protected function casts(): array
    {
        return [
            'saldo_disponible'        => 'decimal:2',
            'calificacion_promedio'   => 'decimal:1',
            'total_viajes'            => 'integer',
            'verificado_dni'          => 'boolean',
            'fecha_verificacion_dni'  => 'datetime',
            'lat_actual'              => 'decimal:7',
            'lng_actual'              => 'decimal:7',
            'ubicacion_actualizada_en'=> 'datetime',
        ];
    }

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
