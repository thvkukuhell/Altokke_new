<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre_completo',
        'apellidos',
        'dni',
        'telefono',
        'email',
        'contrasena_hash',
        'tipo_usuario',
        'activo',
    ];

    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    // Le dice a Laravel que el password está en este campo
    protected string $authPasswordName = 'contrasena_hash';

    protected function casts(): array
    {
        return [
            'contrasena_hash' => 'hashed',
        ];
    }

    // Relaciones
    public function pasajero()
    {
        return $this->hasOne(Pasajero::class, 'id_pasajero', 'id_usuario');
    }

    public function conductor()
    {
        return $this->hasOne(Conductor::class, 'id_conductor', 'id_usuario');
    }

    // Helper — reemplaza tu método iniciales()
    public function iniciales(): string
    {
        $partes = explode(' ', trim($this->nombre_completo));
        return strtoupper(
            substr($partes[0] ?? '', 0, 1) .
            substr($partes[1] ?? '', 0, 1)
        );
    }

    // Le dice a Laravel donde está el password
    public function getAuthPassword(): string
    {
        return $this->contrasena_hash;
    }
}