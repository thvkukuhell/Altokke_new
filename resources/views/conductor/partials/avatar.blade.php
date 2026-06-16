@php
    $usuarioAvatar = $user ?? ($conductor->user ?? auth()->user());
    $avatarTamano = $size ?? 'sidebar';
    $avatarNombre = trim((string) ($usuarioAvatar->nombre_completo ?? 'Conductor'));
    $avatarIniciales = $initials ?? ($usuarioAvatar?->iniciales() ?: '??');
    $avatarFoto = $usuarioAvatar?->foto_perfil_url;
@endphp

<div class="conductor-avatar conductor-avatar--{{ $avatarTamano }}" aria-label="{{ $avatarNombre }}">
    @if($avatarFoto)
        <img src="{{ $avatarFoto }}" alt="{{ $avatarNombre }}" class="conductor-avatar__image">
    @else
        <span class="conductor-avatar__fallback">{{ $avatarIniciales }}</span>
    @endif
</div>
