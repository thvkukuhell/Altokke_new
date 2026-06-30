<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVerificacion extends Model 
{
    protected $table = 'documento_verificacion';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_conductor',
        'tipo_documento',
        'url_archivo',
        'estado_documento',
        'fecha_subida',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_subida'    => 'datetime',
            'fecha_revision'  => 'datetime',
        ];
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class, 'id_conductor', 'id_conductor');
    }
}