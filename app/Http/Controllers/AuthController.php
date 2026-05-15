<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pasajero;
use App\Models\Conductor;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ── Vistas ────────────────────────────────────────

    public function login()
    {
        return view('auth.login');
    }

    public function eleccion_registro()
    {
        return view('auth.eleccion_registro');
    }

    public function registro_pasajero()
    {
        return view('auth.registro_pasajero');
    }

    public function registro_conductor()
    {
        return view('auth.registro_conductor');
    }

    // ── Login ──────────────────────────────────────────

    public function login_proceso(Request $request)
    {
        // Validación
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'El email no tiene formato válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = User::where('email', $request->email)
                    ->where('activo', 1)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->contrasena_hash)) {
            return back()->withErrors([
                'email' => 'Usuario o contraseña incorrectos.'
            ])->withInput(['email' => $request->email]);
        }

        // Login nativo de Laravel
        Auth::login($user);

        // Redirigir según tipo
        return match($user->tipo_usuario) {
            'pasajero'  => redirect()->route('pasajero.solicitarViaje'),
            'conductor' => redirect()->route('conductor.dashboard'),
            default     => redirect('/'),
        };
    }

    // ── Logout ─────────────────────────────────────────

    public function logout()
    {
        Auth::logout();
        return redirect()->route('inicio');
    }

    // ── Registro Pasajero ──────────────────────────────

    public function proc_regist_pasajero(Request $request)
    {
        $request->validate([
            'nombre'             => 'required|string|max:150',
            'apellidos'          => 'nullable|string|max:150',
            'dni'                => 'required|unique:usuarios,dni|min:6',
            'email'              => 'nullable|email|unique:usuarios,email',
            'telefono'           => 'nullable|unique:usuarios,telefono',
            'password'           => 'required|min:8|confirmed',
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'dni.required'       => 'El DNI es obligatorio.',
            'dni.unique'         => 'El DNI ya está registrado.',
            'email.unique'       => 'El email ya está registrado.',
            'telefono.unique'    => 'El teléfono ya está registrado.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // Crear usuario
        $user = User::create([
            'nombre_completo' => $request->nombre,
            'apellidos'       => $request->apellidos,
            'dni'             => preg_replace('/\D/', '', $request->dni),
            'email'           => $request->email,
            'telefono'        => $request->telefono,
            'contrasena_hash' => Hash::make($request->password),
            'tipo_usuario'    => 'pasajero',
        ]);

        // Crear registro en tabla pasajeros
        Pasajero::create([
            'id_pasajero'          => $user->id_usuario,
            'metodo_pago_preferido' => 'efectivo',
        ]);

        Auth::login($user);
        return redirect()->route('pasajero.solicitarViaje');
    }

    // ── Registro Conductor ─────────────────────────────

    public function proc_regist_conductor(Request $request)
    {
        $request->validate([
            'nombre'           => 'required|string|max:150',
            'apellidos'        => 'nullable|string|max:150',
            'dni'              => 'required|unique:usuarios,dni|min:6',
            'email'            => 'required|email|unique:usuarios,email',
            'telefono'         => 'required|unique:usuarios,telefono',
            'password'         => 'required|min:8|confirmed',
            'numero_licencia'  => 'required|string|max:80',
            'placa'            => 'required|string|max:30|unique:vehiculos,placa',
            'numero_soat'      => 'required|string|max:80',
            'marca'            => 'nullable|string|max:80',
            'modelo'           => 'nullable|string|max:80',
            'color'            => 'nullable|string|max:40',
            'year'             => 'nullable|integer|min:1990|max:2030',
        ]);

        $user = User::create([
            'nombre_completo' => $request->nombre,
            'apellidos'       => $request->apellidos,
            'dni'             => preg_replace('/\D/', '', $request->dni),
            'email'           => $request->email,
            'telefono'        => $request->telefono,
            'contrasena_hash' => Hash::make($request->password),
            'tipo_usuario'    => 'conductor',
        ]);

        $conductor = Conductor::create([
            'id_conductor'    => $user->id_usuario,
            'licencia_numero' => $request->numero_licencia,
        ]);

        Vehiculo::create([
            'id_conductor' => $user->id_usuario,
            'placa'        => $request->placa,
            'marca'        => $request->marca,
            'modelo'       => $request->modelo,
            'color'        => $request->color,
            'anio'         => $request->year,
            'numero_soat'  => $request->numero_soat,
        ]);

        Auth::login($user);
        return redirect()->route('conductor.dashboard');
    }
}