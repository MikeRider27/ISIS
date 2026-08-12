<div class="ci-card">
    <div class="ci-card-header">
        <div class="ci-avatar"><i class="fas fa-user"></i></div>
        <div>
            <span class="ci-nombre">{{ $nombrePaciente ?? 'Paciente' }}</span>
            <span class="ci-conteo">
                {{ count($seleccionables) }} recurso{{ count($seleccionables) === 1 ? '' : 's' }} disponible{{ count($seleccionables) === 1 ? '' : 's' }}
            </span>
        </div>
    </div>

    <div class="ci-card-body">
        <form id="vhl-form-generar">
            <input type="hidden" name="bundle_id" value="{{ $bundleId }}">
            <input type="hidden" name="servidor_fhir" value="{{ $servidor }}">

            @if(count($seleccionables) === 0)
                <p class="ci-empty">Este documento no tiene recursos clínicos seleccionables.</p>
            @else
                <p class="vhl-subtitulo">Seleccioná qué compartir en el VHL:</p>
                <div class="vhl-lista-recursos">
                    @foreach($seleccionables as $r)
                        <label class="vhl-recurso">
                            <input type="checkbox" name="seleccionados[]" value="{{ $r['fullUrl'] }}" checked>
                            <span class="vhl-recurso-tipo">{{ $r['tipo'] }}</span>
                            <span class="vhl-recurso-texto">{{ $r['texto'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="vhl-campos-emision">
                    <div>
                        <label for="vhl-pass-code">Código de acceso (PIN)</label>
                        <input type="text" inputmode="numeric" pattern="\d{4,8}" id="vhl-pass-code"
                            name="pass_code" placeholder="1234" required maxlength="8">
                    </div>
                    <div>
                        <label for="vhl-expires-on">Válido hasta</label>
                        <input type="datetime-local" id="vhl-expires-on" name="expires_on" required>
                    </div>
                </div>

                <div class="vhl-buscar-wrap">
                    <button type="submit" id="vhl-boton-generar" class="vhl-btn-buscar">
                        <i class="fas fa-qrcode"></i> GENERAR VHL
                    </button>
                </div>
            @endif
        </form>

        <div id="vhl-qr-resultado"></div>
    </div>
</div>
