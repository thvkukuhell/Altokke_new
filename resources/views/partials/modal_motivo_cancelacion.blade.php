<div id="modal-motivo-cancelacion" class="modal-cancelacion-overlay {{ ($showModal ?? false) || $errors->any() ? '' : 'hidden' }}" aria-hidden="{{ ($showModal ?? false) || $errors->any() ? 'false' : 'true' }}" data-show-modal="{{ ($showModal ?? false) || $errors->any() ? '1' : '0' }}">
    <div class="modal-cancelacion" role="dialog" aria-modal="true" aria-labelledby="modal-cancelacion-titulo">
        <div class="modal-cancelacion-header">
            <div>
                <h2 id="modal-cancelacion-titulo">Confirmar cancelación</h2>
                <p>Indica el motivo de la cancelación para poder mejorar el servicio.</p>
            </div>
            <button type="button" class="modal-cancelacion-close" data-close-cancel-modal aria-label="Cerrar modal">×</button>
        </div>

        <form id="form-modal-cancelacion" method="POST" action="{{ $cancelRoute }}">
            @csrf
            <input type="hidden" name="viaje_id" id="modal-cancelacion-viaje-id" value="{{ old('viaje_id', $viajeId ?? '') }}">

            <div class="modal-cancelacion-body">
                <label for="motivo_cancelacion" class="modal-cancelacion-label">Motivo de cancelación</label>
                <select name="motivo_cancelacion" id="motivo_cancelacion" class="modal-cancelacion-select">
                    <option value="">Selecciona un motivo</option>
                    @php
                        $motivos = [
                            'demora_conductor' => 'El conductor está demorando demasiado.',
                            'pasajero_no_en_punto' => 'El pasajero no se encuentra en el punto de recojo.',
                            'ubicacion_incorrecta' => 'Se ingresó una ubicación incorrecta.',
                            'cambio_opinion' => 'El pasajero cambió de opinión.',
                            'problemas_vehiculo' => 'Problemas con el vehículo.',
                            'otro' => 'Otro motivo.',
                        ];

                        if (($userType ?? null) === 'pasajero') {
                            $motivos = [
                                'demora_conductor' => 'El conductor está demorando demasiado.',
                                'ubicacion_incorrecta' => 'Se ingresó una ubicación incorrecta.',
                                'cambio_opinion' => 'El pasajero cambió de opinión.',
                                'otro' => 'Otro motivo.',
                            ];
                        } elseif (($userType ?? null) === 'conductor') {
                            $motivos = [
                                'pasajero_no_en_punto' => 'El pasajero no se encuentra en el punto de recojo.',
                                'ubicacion_incorrecta' => 'Se ingresó una ubicación incorrecta.',
                                'problemas_vehiculo' => 'Problemas con el vehículo.',
                                'otro' => 'Otro motivo.',
                            ];
                        }
                    @endphp

                    @foreach($motivos as $valor => $texto)
                        <option value="{{ $valor }}" {{ old('motivo_cancelacion') === $valor ? 'selected' : '' }}>
                            {{ $texto }}
                        </option>
                    @endforeach
                </select>
                @error('motivo_cancelacion')
                    <p class="modal-cancelacion-error">{{ $message }}</p>
                @enderror

                <div id="modal-cancelacion-otro-contenedor" class="modal-cancelacion-otro {{ old('motivo_cancelacion') === 'otro' ? '' : 'hidden' }}">
                    <label for="motivo_cancelacion_otro" class="modal-cancelacion-label">Explícanos brevemente</label>
                    <textarea name="motivo_cancelacion_otro" id="motivo_cancelacion_otro" rows="4" placeholder="Describe el motivo...">{{ old('motivo_cancelacion_otro') }}</textarea>
                    @error('motivo_cancelacion_otro')
                        <p class="modal-cancelacion-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="modal-cancelacion-footer">
                <button type="button" class="btn btn-outline" data-close-cancel-modal>Cancelar</button>
                <button type="submit" class="btn btn-verde">Enviar y cancelar viaje</button>
            </div>
        </form>
    </div>
</div>
