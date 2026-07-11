<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    // esto es de Refactorizar Api a ApiController
    public function leerJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $datos = json_decode($input ?: '', true);

        if (! is_array($datos)) {
            return [];
        }

        return $datos;
    }

    public function limpiarBuffersSalida(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    protected function respuestaJson(mixed $datos, int $estado = 200): JsonResponse
    {
        $this->limpiarBuffersSalida();

        return response()->json($datos, $estado);
    }

    protected function exitoJson(string $mensaje, mixed $datos = null, int $estado = 200): JsonResponse
    {
        return $this->respuestaJson([
            'ok' => true,
            'message' => $mensaje,
            'data' => $datos,
        ], $estado);
    }

    protected function errorJson(string $mensaje, int $estado): JsonResponse
    {
        return $this->respuestaJson([
            'ok' => false,
            'message' => $mensaje,
            'error' => $mensaje,
        ], $estado);
    }

    // esto es de Limpieza basica de textos
    protected function limpiarTexto(mixed $texto): string
    {
        return trim(strip_tags((string) $texto));
    }
}
