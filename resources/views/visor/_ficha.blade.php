<div class="visor-panel-ficha">
    <div class="visor-ficha-header">
        <h2>{{ $ficha['titulo'] }}</h2>
        @if($ficha['fecha'])
            <span class="visor-muted">{{ $ficha['fecha'] }}</span>
        @endif
    </div>

    @if($ficha['paciente'])
        <div class="visor-block visor-block--paciente">
            <h4><i class="fas fa-user"></i> Paciente</h4>
            <dl>
                <dt>Nombre</dt>
                <dd>{{ $ficha['paciente']['nombre'] }}</dd>

                <dt>Género</dt>
                <dd>{{ $ficha['paciente']['genero'] }}</dd>

                <dt>Nacimiento</dt>
                <dd>{{ $ficha['paciente']['nacimiento'] }}</dd>

                <dt>Dirección</dt>
                <dd>{{ $ficha['paciente']['direccion'] }}</dd>

                <dt>Teléfono</dt>
                <dd>{{ $ficha['paciente']['telefono'] }}</dd>

                @if(count($ficha['paciente']['identificadores']))
                    <dt>Identificadores</dt>
                    <dd>
                        @foreach($ficha['paciente']['identificadores'] as $id)
                            <span class="visor-badge">@if($id['tipo']){{ $id['tipo'] }}: @endif{{ $id['valor'] }}</span>
                        @endforeach
                    </dd>
                @endif
            </dl>
        </div>
    @endif

    <div class="visor-cols">
        @if($ficha['autor'])
            <div class="visor-block visor-block--medico">
                <h4><i class="fas fa-user-md"></i> Médico</h4>
                <p class="mb-0">{{ $ficha['autor']['nombre'] }}</p>
                <p class="visor-muted">{{ $ficha['autor']['identificador'] }}</p>
            </div>
        @endif

        @if($ficha['organizacion'])
            <div class="visor-block visor-block--institucion">
                <h4><i class="fas fa-hospital"></i> Institución</h4>
                <p class="mb-0">{{ $ficha['organizacion']['nombre'] }}</p>
                <p class="visor-muted mb-0">{{ $ficha['organizacion']['direccion'] }}</p>
                <p class="visor-muted">{{ $ficha['organizacion']['telefono'] }}</p>
            </div>
        @endif
    </div>

    @forelse($ficha['secciones'] as $seccion)
        <div class="visor-block visor-block--{{ $seccion['tipo'] }}">
            <h4>{{ $seccion['titulo'] }}</h4>
            <ul class="visor-list">
                @foreach($seccion['items'] as $item)
                    <li>
                        <strong>
                            {{ $item['texto'] }}
                            @if($item['etiqueta'])
                                <span class="visor-tag visor-tag--{{ $item['etiqueta']['tono'] }}">{{ $item['etiqueta']['texto'] }}</span>
                            @endif
                        </strong>
                        @if($item['detalle'])
                            <span class="visor-muted">{{ $item['detalle'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @empty
        <p class="visor-muted">No se encontraron secciones clínicas en el documento.</p>
    @endforelse
</div>
