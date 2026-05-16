<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

```
Altokke_new
├─ .editorconfig
├─ app
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ AuthController.php
│  │  │  ├─ ConductorController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ InicioController.php
│  │  │  └─ PasajeroController.php
│  │  └─ Middleware
│  │     ├─ EsConductor.php
│  │     └─ EsPasajero.php
│  ├─ Models
│  │  ├─ Calificacion.php
│  │  ├─ Conductor.php
│  │  ├─ Pasajero.php
│  │  ├─ User.php
│  │  ├─ Vehiculo.php
│  │  └─ Viaje.php
│  └─ Providers
│     └─ AppServiceProvider.php
├─ artisan
├─ bootstrap
│  ├─ app.php
│  ├─ cache
│  │  ├─ packages.php
│  │  └─ services.php
│  └─ providers.php
├─ composer.json
├─ composer.lock
├─ config
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ queue.php
│  ├─ services.php
│  └─ session.php
├─ database
│  ├─ database.sqlite
│  ├─ factories
│  │  └─ UserFactory.php
│  ├─ migrations
│  │  ├─ 0001_01_01_000000_create_users_table.php
│  │  ├─ 0001_01_01_000001_create_cache_table.php
│  │  ├─ 0001_01_01_000002_create_jobs_table.php
│  │  ├─ 2026_05_12_030942_create_usuarios_table.php
│  │  ├─ 2026_05_12_030950_create_pasajeros_table.php
│  │  ├─ 2026_05_12_030958_create_conductores_table.php
│  │  ├─ 2026_05_12_031003_create_vehiculos_table.php
│  │  ├─ 2026_05_12_031008_create_viajes_table.php
│  │  ├─ 2026_05_12_031013_create_calificaciones_table.php
│  │  ├─ 2026_05_12_031021_create_notificaciones_table.php
│  │  ├─ 2026_05_12_031026_create_soporte_tickets_table.php
│  │  ├─ 2026_05_12_031033_create_documento_verificacion_table.php
│  │  ├─ 2026_05_12_031038_create_metodo_pago_conductor_table.php
│  │  ├─ 2026_05_12_031043_create_recarga_saldo_table.php
│  │  ├─ 2026_05_12_031048_create_comisiones_table.php
│  │  ├─ 2026_05_12_031055_create_solicitud_viaje_temporal_table.php
│  │  └─ 2026_05_12_031102_create_auditoria_viaje_table.php
│  └─ seeders
│     └─ DatabaseSeeder.php
├─ GUIA.md
├─ package-lock.json
├─ package.json
├─ phpunit.xml
├─ public
│  ├─ .htaccess
│  ├─ favicon.ico
│  ├─ index.php
│  └─ robots.txt
├─ README.md
├─ resources
│  ├─ css
│  │  ├─ app.css
│  │  ├─ auth
│  │  │  ├─ eleccion_registro.css
│  │  │  ├─ login.css
│  │  │  ├─ registro_conductor.css
│  │  │  └─ registro_pasajero.css
│  │  ├─ conductor
│  │  │  ├─ perfil.css
│  │  │  └─ viaje_activo.css
│  │  ├─ global
│  │  │  └─ styles.css
│  │  ├─ inicio
│  │  │  ├─ como_funciona.css
│  │  │  ├─ inicio.css
│  │  │  └─ sobre_nosotros.css
│  │  └─ pasajero
│  │     ├─ buscando_conductor.css
│  │     ├─ calificar_viaje.css
│  │     ├─ editar_perfil.css
│  │     ├─ historial.css
│  │     ├─ pasajero.css
│  │     ├─ perfil.css
│  │     ├─ solicitar_viaje.css
│  │     └─ viaje_en_curso.css
│  ├─ js
│  │  ├─ app.js
│  │  └─ bootstrap.js
│  └─ views
│     ├─ auth
│     │  ├─ eleccion_registro.blade.php
│     │  ├─ login.blade.php
│     │  ├─ registro_conductor.blade.php
│     │  └─ registro_pasajero.blade.php
│     ├─ conductor
│     │  ├─ billetera.blade.php
│     │  ├─ historial_viaje.blade.php
│     │  ├─ inicio.blade.php
│     │  ├─ partials
│     │  │  └─ sidebar.blade.php
│     │  ├─ perfil.blade.php
│     │  ├─ solicitudes.blade.php
│     │  └─ viajde_activo.blade.php
│     ├─ inicio
│     │  ├─ como_funciona.blade.php
│     │  ├─ inicio.blade.php
│     │  └─ sobre_nosotros.blade.php
│     ├─ layouts
│     │  ├─ footer.blade.php
│     │  ├─ footer_inicio.blade.php
│     │  ├─ header_conductor.blade.php
│     │  ├─ header_inicio.blade.php
│     │  ├─ header_pasajero.blade.php
│     │  └─ main.blade.php
│     └─ pasajero
│        ├─ buscando_conductor.blade.php
│        ├─ calificar_viaje.blade.php
│        ├─ editar_perfil.blade.php
│        ├─ historial.blade.php
│        ├─ perfil.blade.php
│        ├─ solicitar_viaje.blade.php
│        └─ viaje_en_curso.blade.php
├─ routes
│  ├─ console.php
│  └─ web.php
├─ storage
│  ├─ app
│  │  ├─ private
│  │  └─ public
│  ├─ framework
│  │  ├─ cache
│  │  │  └─ data
│  │  ├─ sessions
│  │  ├─ testing
│  │  └─ views
│  │     ├─ 46963587457c6df0d5982c0a4aab1847.php
│  │     ├─ 619a479249cd798c8b227c4b7998ebc1.php
│  │     ├─ 856c75b3a4de0e89401d953f178c5a2d.php
│  │     ├─ 88e5c13feb2ca001d9ae99e6a57e3b50.php
│  │     ├─ 9e7e43fece264eb3c20433af4fbee826.php
│  │     └─ ce09fc6dc0c6bf4dbae3e2ae31bff2d1.php
│  └─ logs
├─ tests
│  ├─ Feature
│  │  └─ ExampleTest.php
│  ├─ TestCase.php
│  └─ Unit
│     └─ ExampleTest.php
└─ vite.config.js

```