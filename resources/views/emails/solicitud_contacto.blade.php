<div style="font-family: Arial, sans-serif; color: #0f271f; line-height: 1.6;">
    <h1>Nueva solicitud de ayuda</h1>
    <p>Se ha recibido una nueva solicitud desde el formulario de Ayuda.</p>

    <ul>
        <li><strong>Nombre:</strong> {{ $datos['nombre'] }}</li>
        <li><strong>Correo:</strong> {{ $datos['correo'] }}</li>
        <li><strong>Asunto:</strong> {{ $datos['asunto'] }}</li>
        <li><strong>Tipo de solicitud:</strong> {{ ucfirst($datos['tipo_solicitud']) }}</li>
    </ul>

    <p><strong>Descripción:</strong></p>
    <p>{{ $datos['descripcion'] }}</p>
</div>
