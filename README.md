# 🛵 Altokke
 
Aplicación web de mototaxi bajo demanda, desarrollada con Laravel. Conecta pasajeros y conductores en tiempo real: solicitud de viajes, seguimiento en mapa, historial, calificaciones y comprobantes.
 
## Características
 
- **Roles de usuario**: Pasajero y Conductor, con paneles y flujos independientes.
- **Viajes en tiempo real**: creación, aceptación y seguimiento de viajes mediante WebSockets (Laravel Reverb + Echo).
- **Mapa interactivo**: ubicación en vivo del conductor con Leaflet / OpenStreetMap.
- **Historial y reportes**: historial paginado por usuario, exportación a CSV y comprobante de viaje en PDF.
- **Perfil de usuario**: edición de datos y foto de perfil con almacenamiento persistente.
- **Notificaciones por correo**: resumen de viaje completado (Brevo / SMTP).
- **Calificaciones**: sistema de calificación post-viaje.
## Stack
 
- **Backend:** Laravel 12 (PHP 8.2)
- **Tiempo real:** Laravel Reverb + Laravel Echo
- **Base de datos:** MySQL
- **Frontend:** Blade, Vite, CSS
- **Mapas:** Leaflet / OpenStreetMap
- **Colas:** Laravel Queue (`queue:work`)
- **Despliegue:** Docker + Railway
## Instalación local
 
```bash
git clone https://github.com/thvkukuhell/Altokke_new.git
cd Altokke_new
 
composer install
npm install
 
cp .env.example .env
php artisan key:generate
 
# Configurar DB_* en .env, luego:
php artisan migrate
 
npm run build
php artisan storage:link
```
 
### Correr en desarrollo
 
Se necesitan 4 procesos en paralelo (4 terminales):
 
```bash
php artisan serve       # servidor web
npm run dev             # assets en modo desarrollo
php artisan reverb:start   # servidor WebSocket
php artisan queue:work     # procesamiento de colas
```
 
## ⚙️ Variables de entorno clave
 
```env
APP_URL=
DB_CONNECTION=mysql
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
 
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=
REVERB_SCHEME=
 
BREVO_API_KEY=
BREVO_SENDER_EMAIL=
```
 
## ☁️ Despliegue en producción
 
El proyecto corre en **Railway** con 3 servicios independientes a partir del mismo repositorio:
 
| Servicio | Función | Start Command |
|---|---|---|
| **Altokke_new** | Aplicación web (Apache) | definido en Dockerfile |
| **Worker** | Procesamiento de colas | `php artisan queue:work --sleep=3 --tries=3 --timeout=90` |
| **Reverb** | Servidor WebSocket | `php artisan reverb:start --host=0.0.0.0 --port="$PORT"` |
 
Las fotos de perfil y archivos subidos se almacenan en un **volumen persistente** montado en `storage/app/public`.
 
## Estructura principal
 
```
app/
├─ Events/          # Eventos de viaje (Creado, Aceptado, Actualizado...)
├─ Http/Controllers/ # Lógica de pasajero, conductor, auth
├─ Jobs/            # Tareas en cola
├─ Models/          # Usuario, Viaje, Conductor, Pasajero, Vehículo...
└─ Services/        # Lógica de negocio (ViajeService)
 
resources/views/    # Vistas Blade por rol (pasajero/conductor/auth)
routes/             # web.php, api.php, channels.php
database/migrations/
```
 
## Equipo
 
Proyecto desarrollado como parte del curso de Desarrollo de Aplicaciones Web II — UNTRM.
Estudiantes:
    - Cullampe Mendoza Alexander
    - Garro Gómez Elvita Donina
    - Mas Tuesta Hellen Shanela
    - Sandoval Nuñez Juan Carlos
 
## 📄 Licencia
 
Uso académico.
