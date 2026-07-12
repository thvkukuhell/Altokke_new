@php
    $usuarioAvatar = $user ?? ($conductor->user ?? auth()->user());
    $avatarTamano = $size ?? 'sidebar';

    $avatarNombre = trim(
        (string) ($usuarioAvatar->nombre_completo ?? 'Conductor')
    );

    $avatarIniciales = $initials
        ?? ($usuarioAvatar?->iniciales() ?: 'C');


    $avatarFoto = $usuarioAvatar?->foto_perfil_url;
@endphp

<div
    class="conductor-avatar conductor-avatar--{{ $avatarTamano }}"
    aria-label="{{ $avatarNombre }}"
>
    <img
        src="{{ $avatarFoto ?? '' }}"
        alt="Foto de {{ $avatarNombre }}"
        class="conductor-avatar__image"
        data-profile-photo-image
        @if(!$avatarFoto)
            style="display: none;"
        @endif
        onerror="
            this.style.display = 'none';

            const fallback = this.parentElement.querySelector(
                '[data-profile-photo-placeholder]'
            );

            if (fallback) {
                fallback.style.display = 'grid';
                fallback.hidden = false;
            }
        "
    >

    <span
        class="conductor-avatar__fallback"
        data-profile-photo-placeholder
        style="display: {{ $avatarFoto ? 'none' : 'grid' }};"
    >
        {{ $avatarIniciales }}
    </span>
</div>