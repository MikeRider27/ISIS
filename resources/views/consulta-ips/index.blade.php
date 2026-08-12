@extends('adminlte::page')

@section('title', 'Consultar IPS')

@section('content_header')
    <h1>Consultar IPS (ITI-67)</h1>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .ci-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .ci-search-box {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .ci-search-box i.fa-magnifying-glass {
            color: #6c757d;
        }

        .ci-search-box input {
            flex: 1;
            border: 0;
            outline: none;
            font-size: 1rem;
            background: transparent;
        }

        .ci-buscar-wrap {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .ci-btn-buscar {
            background: #1b3a63;
            color: #fff;
            border: 0;
            border-radius: 2rem;
            padding: .65rem 2.75rem;
            font-weight: 700;
            letter-spacing: .03em;
            font-size: .85rem;
            cursor: pointer;
        }

        .ci-btn-buscar:hover {
            background: #142c4d;
        }

        .ci-btn-buscar:disabled {
            opacity: .6;
            cursor: default;
        }

        .ci-empty {
            text-align: center;
            color: #6c757d;
            padding: 2rem;
        }

        .ci-empty i {
            font-size: 2rem;
            margin-bottom: .75rem;
            display: block;
        }

        .ci-error {
            color: #dc3545;
        }

        .ci-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            overflow: hidden;
        }

        .ci-card-header {
            display: flex;
            align-items: center;
            gap: .85rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fc;
        }

        .ci-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #eef2fd;
            color: #4e73df;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .ci-nombre {
            display: block;
            font-weight: 600;
            color: #212529;
        }

        .ci-conteo {
            color: #6c757d;
            font-size: .8rem;
        }

        .ci-card-body {
            padding: .25rem 1.25rem 1.1rem;
        }

        .ci-tabla thead th {
            border-top: 0;
        }

        .ci-fecha i {
            color: #adb5bd;
            margin-right: .5rem;
        }

        .ci-btn-ver {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: transparent;
            color: #1b3a63;
            border: 1px solid #1b3a63;
            border-radius: .35rem;
            padding: .4rem 1rem;
            font-weight: 600;
            font-size: .82rem;
            text-decoration: none;
            white-space: nowrap;
        }

        .ci-btn-ver:hover {
            background: #1b3a63;
            color: #fff;
        }
    </style>
@stop

@section('content')
    <div class="ci-wrap">
        <form method="GET" action="{{ route('consulta-ips') }}" id="ci-form">
            <div class="ci-search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" name="identificador" id="ci-input" placeholder="Identificador del paciente"
                    value="{{ $identificador }}" autocomplete="off">
            </div>

            <div class="ci-buscar-wrap">
                <button type="submit" class="ci-btn-buscar" id="ci-boton">BUSCAR</button>
            </div>
        </form>

        <div id="ci-resultados">
            @if($error)
                <div class="ci-empty ci-error">
                    <i class="fas fa-triangle-exclamation"></i>
                    <p>{{ $error }}</p>
                </div>
            @elseif($documentos !== null)
                @include('consulta-ips._resultados', ['documentos' => $documentos, 'nombrePaciente' => $nombrePaciente])
            @endif
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('ci-form');
            const input = document.getElementById('ci-input');
            const boton = document.getElementById('ci-boton');
            const resultados = document.getElementById('ci-resultados');
            const urlBuscar = @json(route('consulta-ips.buscar'));
            const token = @json(csrf_token());

            function estadoErrorResultados(mensaje) {
                return `<div class="ci-empty ci-error"><i class="fas fa-triangle-exclamation"></i><p>${mensaje}</p></div>`;
            }

            function inicializarTabla() {
                const tabla = document.getElementById('ci-tabla');

                if (tabla && window.jQuery) {
                    window.jQuery(tabla).DataTable({
                        pageLength: 10,
                        lengthChange: false,
                        searching: false,
                        order: [[1, 'desc']],
                        language: {
                            zeroRecords: 'No se encontraron resultados',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ documentos',
                            infoEmpty: 'No hay documentos',
                            paginate: { previous: 'Anterior', next: 'Siguiente' },
                        },
                    });
                }
            }

            async function buscar(identificador) {
                boton.disabled = true;
                resultados.innerHTML = '<div class="ci-empty"><i class="fas fa-spinner fa-spin"></i><p>Buscando…</p></div>';

                try {
                    const response = await fetch(urlBuscar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: 'identificador=' + encodeURIComponent(identificador),
                    });

                    const data = await response.json();

                    resultados.innerHTML = response.ok
                        ? data.html
                        : estadoErrorResultados(data.error ?? 'No se pudo realizar la búsqueda.');

                    if (response.ok) {
                        inicializarTabla();
                    }
                } catch (e) {
                    resultados.innerHTML = estadoErrorResultados('No se pudo conectar con el servidor.');
                } finally {
                    boton.disabled = false;
                }
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const identificador = input.value.trim();
                if (identificador) {
                    buscar(identificador);
                }
            });

            inicializarTabla();
        })();
    </script>
@stop
