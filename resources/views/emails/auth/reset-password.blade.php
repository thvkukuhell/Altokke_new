<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Restablecer contraseña</title>
</head>
<body style="margin:0; padding:0; background:#f4f7f5; font-family:Arial, Helvetica, sans-serif; color:#10221a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f7f5; padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px; background:#ffffff; border:1px solid #dfe7e2; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td style="padding:28px 30px 18px;">
                            <p style="margin:0 0 10px; font-size:14px; font-weight:700; color:#17a34a;">Altokke</p>
                            <h1 style="margin:0; font-size:26px; line-height:1.2; color:#10221a;">Restablece tu contraseña</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 26px;">
                            <p style="margin:0 0 18px; font-size:16px; line-height:1.55; color:#34483e;">
                                Recibimos una solicitud para cambiar la contraseña de tu cuenta en Altokke.
                            </p>
                            <p style="margin:0 0 24px; font-size:16px; line-height:1.55; color:#34483e;">
                                El enlace estará disponible durante {{ $expires }} minutos.
                            </p>
                            <p style="margin:0 0 26px;">
                                <a href="{{ $url }}" style="display:inline-block; background:#17a34a; color:#ffffff; text-decoration:none; font-weight:700; font-size:16px; padding:13px 20px; border-radius:10px;">
                                    Restablecer contraseña
                                </a>
                            </p>
                            <p style="margin:0 0 10px; font-size:14px; line-height:1.5; color:#5d6f65;">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:
                            </p>
                            <p style="margin:0 0 22px; font-size:13px; line-height:1.5; word-break:break-all; color:#1b6d3a;">
                                {{ $url }}
                            </p>
                            <p style="margin:0; font-size:14px; line-height:1.5; color:#5d6f65;">
                                Si no solicitaste este cambio, puedes ignorar este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
