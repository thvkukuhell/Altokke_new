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

```
Altokke_new
├─ .editorconfig
├─ app
│  ├─ Events
│  │  ├─ ConductorMovido.php
│  │  ├─ ViajeAceptado.php
│  │  ├─ ViajeActualizado.php
│  │  └─ ViajeCreado.php
│  ├─ Http
│  │  ├─ Controllers
│  │  │  ├─ AuthController.php
│  │  │  ├─ ConductorController.php
│  │  │  ├─ Controller.php
│  │  │  ├─ InicioController.php
│  │  │  └─ PasajeroController.php
│  │  └─ Middleware
│  │     ├─ CheckRole.php
│  │     └─ RedirectIfAuthenticatedRole.php
│  ├─ Jobs
│  │  ├─ IniciarViaje.php
│  │  └─ SimularLlegadaConductor.php
│  ├─ Models
│  │  ├─ Calificacion.php
│  │  ├─ Comision.php
│  │  ├─ Conductor.php
│  │  ├─ ConfiguracionTarifa.php
│  │  ├─ DocumentoVerificacion.php
│  │  ├─ Notificacion.php
│  │  ├─ Pasajero.php
│  │  ├─ RecargaSaldo.php
│  │  ├─ User.php
│  │  ├─ Vehiculo.php
│  │  └─ Viaje.php
│  ├─ Providers
│  │  └─ AppServiceProvider.php
│  └─ Services
│     └─ ViajeService.php
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
│  │  ├─ 2026_05_12_031102_create_auditoria_viaje_table.php
│  │  ├─ 2026_05_19_174541_add_foto_perfil_to_usuarios_table.php
│  │  ├─ 2026_05_20_151034_add_expirado_toestado_viaje.php
│  │  ├─ 2026_06_06_103146_replace_user_cascade_deletes_with_restrict.php
│  │  ├─ 2026_06_07_150204_add_soft_deletes_to_main_tables.php
│  │  └─ 2026_06_07_152944_create_configuracion_tarifas_table.php
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
│  │  ├─ logo_moto.png
│  │  └─ perfil.png
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
│     │  ├─ recuperar_contrasena.blade.php
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
│  ├─ api.php
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
│  │     ├─ 05a67caeffc800615a2ea03dfff23670.php
│  │     ├─ 07a5a73fac1fe5633bcbd542555ed26b.php
│  │     ├─ 09c76156c24269ffd27b713a1630c3e7.php
│  │     ├─ 0d41238f2b0cc2f222b902738ef331be.php
│  │     ├─ 0e01b2547c79a66dc560717e3b85b19e.php
│  │     ├─ 119f15969b1761badbbe7ab59842038a.php
│  │     ├─ 1b6754e8acdbfa2eda5856380f57d5a3.php
│  │     ├─ 1cb48ae648fd11b71fa27dedcd6192f7.php
│  │     ├─ 1cfb4e1dbab6c0dd7920d6447b23ba57.php
│  │     ├─ 1e19a95a557bcf68c309434eac2854d9.php
│  │     ├─ 228295662fb40797cd5156f3b0636d97.php
│  │     ├─ 2c33cca7812918b4dc99ba6d9e7e0247.php
│  │     ├─ 321543e40e772d2b1b22711c9844cdec.php
│  │     ├─ 37e4f048c59a5ef6d603b86604145315.php
│  │     ├─ 381a81234c9c5458101c1568a8b80fd4.php
│  │     ├─ 39fbfbbdea7ead1b36440ee251b0c69c.php
│  │     ├─ 3d60a75db4fe78ee8ba60270bb1e489e.php
│  │     ├─ 3ebcaa42af1bb617c8e8c3dd259a2202.php
│  │     ├─ 43f1abe41e4220e34a96df7d632dac51.php
│  │     ├─ 46963587457c6df0d5982c0a4aab1847.php
│  │     ├─ 4c412f40a89695b1deef669b74d7e163.php
│  │     ├─ 4ccf7236285ddbc6a2c25fd9191be705.php
│  │     ├─ 4fb5881eb2a15a051f14a66da0931d37.php
│  │     ├─ 5a267aabdac9c5ec9b66e6b070080608.php
│  │     ├─ 5d348318dd24170c47f9c4eb45910768.php
│  │     ├─ 5ed3165e9349d6b6dc69acaa7f5aede2.php
│  │     ├─ 619a479249cd798c8b227c4b7998ebc1.php
│  │     ├─ 63cb476e315be630d229d4873d39750f.php
│  │     ├─ 6731e59b63b28877d7a2f52f19d42468.php
│  │     ├─ 6c2dfc2e9c23806df1bbd885427bccfb.php
│  │     ├─ 6e17df5470816b55023a224051d60679.php
│  │     ├─ 7148a581cfb8bd6db48b54fe5ddc473d.php
│  │     ├─ 7364008258c64530b6382005beba56c8.php
│  │     ├─ 75c0eac71945905d09b1b3c4fee30ce5.php
│  │     ├─ 76441fdd160e2d75a76a88aeac40a3a6.php
│  │     ├─ 7f28eb4c84f3095fff8f6fb7b11f6874.php
│  │     ├─ 817dcda2c41e26e0148990922eb8dc64.php
│  │     ├─ 856c75b3a4de0e89401d953f178c5a2d.php
│  │     ├─ 85856ce8ff7da95d5bdf19302034ccb7.php
│  │     ├─ 88e5c13feb2ca001d9ae99e6a57e3b50.php
│  │     ├─ 8aaa001c49143f8aec84d7bcb78c29db.php
│  │     ├─ 8ab9a2c5d85d1231c3c565dcc7d5485c.php
│  │     ├─ 9e7e43fece264eb3c20433af4fbee826.php
│  │     ├─ a31af4421c74b189c10a593ffd53cbe3.php
│  │     ├─ a439e22dabef5f25911d7476a9945046.php
│  │     ├─ aaf8bfe5d3ef112e8114776992f13143.php
│  │     ├─ b0ca3630e5304f9b103a3d51327a1610.php
│  │     ├─ b23116b6a8cee41362bd3022ca359cdc.php
│  │     ├─ bd15ad5cc9cc8a5bb2f023f87fa5b22c.php
│  │     ├─ c7cd5436d68d1fd5b731840b855fc293.php
│  │     ├─ c960fecc6a52678e9aa135c2330e6402.php
│  │     ├─ cc8e4cb4e89d44eee765823525deb47f.php
│  │     ├─ ce09fc6dc0c6bf4dbae3e2ae31bff2d1.php
│  │     ├─ ce5d201133280a0fd9d4b23b3acd5bb6.php
│  │     ├─ e2c10f18b075d10edad7ae11e9cb9605.php
│  │     ├─ e5cab8ff775660218ed9cf673ceb0d0d.php
│  │     ├─ e923d467c79eeffc90de58932694d8fe.php
│  │     ├─ eb51f44e1dc9dccd468a66b1373afccf.php
│  │     ├─ f3d70a6f7482aef8f78967f1c398abcc.php
│  │     ├─ f67c17ffa2a84b153a11ea67275867ce.php
│  │     ├─ fa42ea7693aa355dc9cb3d96ccb42f76.php
│  │     ├─ fb14ecb5f6ecda2f10e276ac6a769c31.php
│  │     ├─ fc51d29ad136b26ace7fa2caaccd9918.php
│  │     ├─ fcf1a307cbe7509f85bfbeab5f197632.php
│  │     ├─ fd3c40e5639eda76c4abc34cc355ba87.php
│  │     └─ fdeb16894ced8ec2f0c9000de0e4505e.php
│  └─ logs
├─ tests
│  ├─ Feature
│  │  └─ ExampleTest.php
│  ├─ TestCase.php
│  └─ Unit
│     └─ ExampleTest.php
└─ vite.config.js

```