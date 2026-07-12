<?php

namespace App\Http\Controllers;

use App\Models\Viaje;
use App\Services\SimplePdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    public function pasajeroHistorialCsv(): Response
    {
        $viajes = Viaje::where('id_pasajero', Auth::id())
            ->with('conductor.user')
            ->orderByDesc('fecha_solicitud')
            ->get();

        return $this->descargarCsv('historial_pasajero.csv', $this->filasHistorial($viajes, 'conductor'));
    }

    public function conductorHistorialCsv(): Response
    {
        $viajes = Viaje::where('id_conductor', Auth::id())
            ->with('pasajero.user')
            ->orderByDesc('fecha_solicitud')
            ->get();

        return $this->descargarCsv('historial_conductor.csv', $this->filasHistorial($viajes, 'pasajero'));
    }

    public function comprobanteViajePdf(int $viajeId, SimplePdfService $pdfService): Response
    {
        $viaje = Viaje::with(['pasajero.user', 'conductor.user', 'conductor.vehiculo'])
            ->where('id_viaje', $viajeId)
            ->firstOrFail();

        abort_unless(Auth::user()?->can('downloadReport', $viaje), 403);

        $pdf = $pdfService->generar('Comprobante de viaje Altokke', [
            'Codigo de viaje: #' . $viaje->id_viaje,
            'Estado: ' . ucfirst($viaje->estado_viaje),
            'Pasajero: ' . ($viaje->pasajero->user->nombre_completo ?? 'Pasajero'),
            'Conductor: ' . ($viaje->conductor->user->nombre_completo ?? 'Conductor'),
            'Vehiculo: ' . trim(($viaje->conductor->vehiculo->marca ?? '') . ' ' . ($viaje->conductor->vehiculo->modelo ?? '')),
            'Placa: ' . ($viaje->conductor->vehiculo->placa ?? '-'),
            'Origen: ' . $viaje->origen_texto,
            'Destino: ' . $viaje->destino_texto,
            'Distancia: ' . ($viaje->distancia_km ? $viaje->distancia_km . ' km' : '-'),
            'Tiempo estimado: ' . ($viaje->tiempo_estimado_min ? $viaje->tiempo_estimado_min . ' min' : '-'),
            'Metodo de pago: ' . ucfirst($viaje->metodo_pago ?? '-'),
            'Tarifa: S/ ' . number_format((float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0), 2),
            'Fecha de fin: ' . ($viaje->fecha_fin?->format('d/m/Y H:i') ?? '-'),
        ]);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="comprobante_viaje_' . $viaje->id_viaje . '.pdf"',
        ]);
    }

    private function filasHistorial($viajes, string $persona): array
    {
        $filas = [[
            'ID',
            'Fecha',
            'Estado',
            'Origen',
            'Destino',
            ucfirst($persona),
            'Metodo de pago',
            'Distancia km',
            'Tiempo min',
            'Tarifa',
        ]];

        foreach ($viajes as $viaje) {
            $filas[] = [
                $viaje->id_viaje,
                $viaje->fecha_solicitud?->format('d/m/Y H:i') ?? '',
                $viaje->estado_viaje,
                $viaje->origen_texto,
                $viaje->destino_texto,
                $persona === 'conductor'
                    ? ($viaje->conductor->user->nombre_completo ?? '')
                    : ($viaje->pasajero->user->nombre_completo ?? ''),
                $viaje->metodo_pago,
                $viaje->distancia_km,
                $viaje->tiempo_estimado_min,
                number_format((float) ($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0), 2),
            ];
        }

        return $filas;
    }

    private function descargarCsv(string $nombre, array $filas): Response
    {
        $handle = fopen('php://temp', 'r+');

        foreach ($filas as $fila) {
            fputcsv($handle, array_map([$this, 'sanitizarCeldaCsv'], $fila));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    private function sanitizarCeldaCsv(mixed $valor): mixed
    {
        if (!is_string($valor) || $valor === '') {
            return $valor;
        }

        if (in_array($valor[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $valor;
        }

        return $valor;
    }
}
