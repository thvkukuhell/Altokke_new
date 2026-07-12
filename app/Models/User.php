<?php
namespace App\Models;

use App\Notifications\BrevoResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

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
        'foto_perfil',
    ];

    protected $hidden = [
        'contrasena_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->contrasena_hash;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new BrevoResetPasswordNotification($token));
    }

    protected function casts(): array
    {
        return [
            'activo'          => 'boolean',
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

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    // Profile photo URL
    public function getFotoPerfilUrlAttribute(): ?string
    {
        if (!is_string($this->foto_perfil) || trim($this->foto_perfil) === '') {
            return null;
        }

        return Storage::disk('public')->exists($this->foto_perfil)
            ? Storage::url($this->foto_perfil)
            : null;
    }

    public function iniciales(): string
    {
        $partes = explode(' ', trim($this->nombre_completo));
        return strtoupper(
            substr($partes[0] ?? '', 0, 1) .
            substr($partes[1] ?? '', 0, 1)
        );
    }

}
