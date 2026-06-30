import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/auth/registro_conductor.js',
                'resources/js/inicio/inicio.js',
                'resources/js/conductor/solicitudes.js',
                'resources/js/conductor/viaje_activo.js',
                'resources/js/pasajero/buscando_conductor.js',
                'resources/js/pasajero/historial.js',
                'resources/js/pasajero/solicitar_viaje.js',
                'resources/js/pasajero/viaje_en_curso.js',
                'resources/js/layouts/header_conductor.js',
                'resources/js/layouts/header_pasajero.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
