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
│  ├─ Events
│  │  ├─ ConductorMovido.php
│  │  ├─ ViajeAceptado.php
│  │  └─ ViajeCreado.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ AuthController.php
│  │  │  ├─ ConductorController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ InicioController.php
│  │  │  └─ PasajeroController.php
│  │  └─ Middleware
│  │     └─ CheckRole.php
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
│  ├─ broadcasting.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ queue.php
│  ├─ reverb.php
│  ├─ services.php
│  └─ session.php
├─ database
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
│  ├─ img
│  │  ├─ email.png
│  │  ├─ estrella.png
│  │  ├─ icon_phone.jpg
│  │  ├─ location.png
│  │  ├─ login_client_icon.png
│  │  ├─ logoTemporal.png
│  │  └─ user.png
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
│  │  │  └─ inicio.css
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
│  │  ├─ bootstrap.js
│  │  └─ echo.js
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
│     │  └─ viaje_activo.blade.php
│     ├─ inicio
│     │  └─ inicio.blade.php
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
│  ├─ channels.php
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
│  │     ├─ 01ac1180de98c29bc883e6d7251ffe22.php
│  │     ├─ 086e8a3079dd1dd23ac4bf1cb73ed8a2.php
│  │     ├─ 0b384e8b726d5eef90f7097771c9656f.php
│  │     ├─ 0bf14c9480d1a5e36e64b1e05e17aa1e.php
│  │     ├─ 10b863c0f93873d90e0cbf875b2e4838.php
│  │     ├─ 16313211658304dae025509d32cbdd5f.php
│  │     ├─ 181c4e199cb14e9015aacd6a0461b487.php
│  │     ├─ 18c2b321fbab193b25a4b5343857c363.php
│  │     ├─ 1a16b386671b506968ddfdc09b9f9cc3.php
│  │     ├─ 1ca6dbf68b8a9088b7ae3b197fef593e.php
│  │     ├─ 1d858acad0c9153e05960a78f92e751b.php
│  │     ├─ 27e29cd86c798536c4fc90d7dc315e6c.php
│  │     ├─ 288240d56d8d43201b0c11c3890ea1ba.php
│  │     ├─ 2b075dba86bb7009f0e76f5635e7e4a9.php
│  │     ├─ 2bf8f9c2bcf660effbca1189f92a99a5.php
│  │     ├─ 3158ace2bf9ffc260cfcc15572b28288.php
│  │     ├─ 31979f4e5810dbfc68150c5a17383a10.php
│  │     ├─ 41bd7f38da7472d2226e139322a909fa.php
│  │     ├─ 42cefa4d80b12dbbd2ebe80e79a3fc98.php
│  │     ├─ 45159b5522524b21ca7846f89b4ad925.php
│  │     ├─ 4a0fcf6c93a587712d16566d9e75cd83.php
│  │     ├─ 52e232013ca327670c3b5d1d355eab85.php
│  │     ├─ 548e879a2b108ca054dc353ec6871eb4.php
│  │     ├─ 5a6b17977d2c201a25b8dced1760dc79.php
│  │     ├─ 5be5f90fd6932bd1d81dd04794528b57.php
│  │     ├─ 5cf19add79ccc138bbec362b6a62f061.php
│  │     ├─ 662ede64f987b9dfccde5b1b957bd763.php
│  │     ├─ 6a046b02fe000f68320249c09e8d093c.php
│  │     ├─ 6b9c37a8b85e7f3ce3e0c210d395ac57.php
│  │     ├─ 6d71ef274ebfa2661ad93229f6b24163.php
│  │     ├─ 74d0fbed0987e02602b02b4d235dfe1f.php
│  │     ├─ 8a0086261f9cbc971f82af8227474b33.php
│  │     ├─ 8a67137dc392341be4a46bdd9dc3b72d.php
│  │     ├─ 97f06445bbeaa0526d625cb3f413347b.php
│  │     ├─ 989782c25d5cde0c2555c648cf6b1bdc.php
│  │     ├─ 9a3a81532b5fb856bc416e68b6aa4017.php
│  │     ├─ a06605fcd0333afff86047ed2aab0a71.php
│  │     ├─ a5bfc576f99288ee4faa01ed7379562b.php
│  │     ├─ ab366d49355ef6a907dbd9970bcdab7c.php
│  │     ├─ ab45d995893e62e28d6a092720041f84.php
│  │     ├─ ab8a8827ba9dcc3467ca1c9cbf7a5c47.php
│  │     ├─ abff73dfac75cf913c565ee9a27d55ae.php
│  │     ├─ b18a83b0b2404fdebc8d933681f80a0d.php
│  │     ├─ b235c0463c06185bd4d026bf9423fd3b.php
│  │     ├─ b31f94baa37d6919c8b12ff87fe2662e.php
│  │     ├─ b9d7e27533b3857591c100e73e564e94.php
│  │     ├─ bab7a086182fefcbeef7f80c38ec86f8.php
│  │     ├─ cabf26ffa448282f268ebb6e5043c505.php
│  │     ├─ cf794d118c2ee4fc5c47a8bff23b8ab0.php
│  │     ├─ d2b03b886fd106920bc610e917238d02.php
│  │     ├─ d3111a460dcbc3f644e20debefb34b78.php
│  │     ├─ d3e5b13d83739779449776d03047396c.php
│  │     ├─ d60d9d5051eb17fe4000fa6ff66c7ad1.php
│  │     ├─ d61964f7d08515c87359a373d700a544.php
│  │     ├─ d76ce8073a6e902d5193b84758fb97eb.php
│  │     ├─ de529c7f710db088a68513aca65bd8ef.php
│  │     ├─ deaecd7a52f005132229d366b4a77606.php
│  │     ├─ df87936df939f6efded4931f803f49ac.php
│  │     ├─ e467ce1d2feabb3b0ec3350257c74a9a.php
│  │     ├─ e96071b786dc843037f78e4c811a45f1.php
│  │     ├─ ea33f4df8e141252e5088ebd171e93db.php
│  │     ├─ ee9f19c9b56f27ec36f4bc284e2c7dee.php
│  │     ├─ f24ff79d4be93f0ca300a817d6867b19.php
│  │     └─ f73b703e1354056a27ae63c778ba6e79.php
│  └─ logs
├─ tests
│  ├─ Feature
│  │  └─ ExampleTest.php
│  ├─ TestCase.php
│  └─ Unit
│     └─ ExampleTest.php
└─ vite.config.js

```
