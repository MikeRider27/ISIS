@if(count($documentos) === 0)
    <div class="ci-empty">
        <i class="fas fa-folder-open"></i>
        <p>No se encontraron documentos para este identificador.</p>
    </div>
@else
    <div class="ci-card">
        <div class="ci-card-header">
            <div class="ci-avatar"><i class="fas fa-user"></i></div>
            <div>
                <span class="ci-nombre">{{ $nombrePaciente ?? 'Paciente' }}</span>
                <span class="ci-conteo">
                    {{ count($documentos) }} documento{{ count($documentos) === 1 ? '' : 's' }} encontrado{{ count($documentos) === 1 ? '' : 's' }}
                </span>
            </div>
        </div>

        <div class="ci-card-body">
            <table id="ci-tabla" class="table table-hover ci-tabla" style="width: 100%">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th class="text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documentos as $doc)
                        <tr>
                            <td>{{ $doc['titulo'] }}</td>
                            <td data-order="{{ $doc['fecha'] }}" class="ci-fecha">
                                <i class="far fa-calendar"></i>{{ $doc['fecha_formateada'] ?? 'Sin fecha' }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('visor', ['id' => $doc['id']]) }}" class="ci-btn-ver">
                                    <i class="fas fa-eye"></i> Ver IPS/{{ $doc['id'] }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
