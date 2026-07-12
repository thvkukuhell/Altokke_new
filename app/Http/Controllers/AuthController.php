<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pasajero;
use App\Models\Conductor;
use App\Models\DocumentoVerificacion;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Throwable;

class AuthController extends Controller
{
    private const PASSWORD_RESET_GENERIC_MESSAGE = 'Si el correo se encuentra registrado, recibirás las instrucciones para restablecer tu contraseña.';

    // Vistas 
    public function login()
    {
        return view('auth.login', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/login.css'],
        ]);
    }

    public function eleccion_registro()
    {
        return view('auth.eleccion_registro', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/eleccion_registro.css'],
        ]);
    }

    public function registro_pasajero()
    {
        return view('auth.registro_pasajero', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/registro_pasajero.css'],
        ]);
    }

    public function registro_conductor()
    {
        return view('auth.registro_conductor', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/registro_conductor.css'],
        ]);
    }

    // Login 
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
        $request->session()->regenerate();

        // Redirigir según tipo
        return match($user->tipo_usuario) {
            'pasajero'  => redirect()->route('pasajero.solicitarViaje'),
            'conductor' => redirect()->route('conductor.dashboard'),
            default     => redirect('/'),
        };
    }

    // Logout 
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio');
    }

    // Registro Pasajero
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
        $request->session()->regenerate();
        return redirect()->route('pasajero.solicitarViaje');
    }

    // Registro Conductor
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
            'estado_conductor' => 'activo',
            'verificado_dni' => 1,
            'fecha_verificacion_dni' => now(),
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

        foreach ([
            'dni' => $user->dni,
            'licencia_conducir' => $conductor->licencia_numero,
            'soat' => $request->numero_soat,
            'tarjeta_propiedad' => $request->placa,
        ] as $tipo => $referencia) {
            DocumentoVerificacion::create([
                'id_conductor' => $user->id_usuario,
                'tipo_documento' => $tipo,
                'url_archivo' => 'registro-simulado:' . $referencia,
                'estado_documento' => 'aprobado', 
                'fecha_revision' => now(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('conductor.dashboard');
    }

    // Recuperar password
    public function recuperarPassword()
    {
        return view('auth.recuperar_password', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/login.css'], 
        ]);
    }

    public function recuperarPasswordProceso(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower((string) $request->input('email'));
        $emailHash = hash('sha256', $email);

        Log::info('Password reset link requested.', [
            'email_hash' => $emailHash,
            'provider' => 'brevo',
        ]);

        try {
            $status = PasswordBroker::sendResetLink($request->only('email'));

            Log::info('Password reset broker finished.', [
                'email_hash' => $emailHash,
                'status' => $status,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            Log::error('Password reset mail transport failed.', [
                'email_hash' => $emailHash,
                'exception' => $exception::class,
                'code' => $exception->getCode(),
            ]);
        }

        return back()
            ->withInput($request->only('email'))
            ->with('exito', 'Si el correo se encuentra registrado, recibirás las instrucciones para restablecer tu contraseña.');
    }

    public function mostrarRestablecerPassword(Request $request, string $token)
    {
        return view('auth.recuperar_password', [
            'header' => 'header_inicio',
            'footer' => 'footer_inicio',
            'css'    => ['auth/login.css'],
            'token'  => $token,
            'email'  => $request->query('email'),
        ]);
    }

    public function restablecerPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'contrasena_hash' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('login')
            ->with('exito', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}
