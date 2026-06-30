<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de viaje Altokke</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827; background:#f8fafc; padding:24px;">
    <div style="max-width:620px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; padding:24px;">
        <h1 style="margin:0 0 8px; color:#2d6a2d; font-size:24px;">Viaje completado</h1>
        <p style="margin:0 0 20px; color:#4b5563;">Este es el resumen de tu viaje en Altokke.</p>

        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="padding:8px 0; color:#6b7280;">Codigo</td>
                <td style="padding:8px 0; text-align:right;">#{{ $viaje->id_viaje }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; color:#6b7280;">Origen</td>
                <td style="padding:8px 0; text-align:right;">{{ $viaje->origen_texto }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; color:#6b7280;">Destino</td>
                <td style="padding:8px 0; text-align:right;">{{ $viaje->destino_texto }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; color:#6b7280;">Conductor</td>
                <td style="padding:8px 0; text-align:right;">{{ $viaje->conductor->user->nombre_completo ?? 'Conductor' }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0; color:#6b7280;">Tarifa</td>
                <td style="padding:8px 0; text-align:right; font-weight:700;">S/ {{ number_format($viaje->tarifa_final ?? $viaje->tarifa_estimada ?? 0, 2) }}</td>
            </tr>
        </table>

        <p style="margin:20px 0 0; color:#6b7280; font-size:13px;">
            Si necesitas revisar mas detalles, ingresa a tu historial de viajes en Altokke.
        </p>
    </div>
</body>
</html>
