<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altokke</title>
    <meta name="description" content="Pide tu mototaxi en Bagua en segundos. Conductores verificados, precios claros y servicio rápido y seguro.">
    <meta name="keywords" content="mototaxi Bagua, taxi Bagua, transporte Bagua, mototaxi rápido, Bagua Perú">
    <meta name="author" content="CULLAMPE MENDOZA ALEXANDER, GARRO GOMEZ ELVITA DONINA, MAS TUESTA HELLEN SHANELA, SANDOVAL NUÑEZ JUAN CARLOS">

    {{-- Vite (CSS + JS global) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    {{-- HEADER --}}
    @isset($header)
        @include('layouts.' . $header)
    @endisset

    {{-- CONTENIDO PRINCIPAL (Sin contenedores que limiten el ancho aquí) --}}
    <main class="main-wrapper">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    @isset($footer)
        @include('layouts.' . $footer)
    @endisset

</body>
</html>