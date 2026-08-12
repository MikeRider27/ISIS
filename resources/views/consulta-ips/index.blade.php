@extends('adminlte::page')

@section('title', 'Consultar IPS')

@section('content_header')
    <div style="text-align: center; width: 100%;">
        <h1 style="display: inline-block;">
            <i class="fas fa-file-medical-alt"></i> Consultar IPS (ITI-67)
        </h1>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
    <style>
        /* ====== CONTENEDOR PRINCIPAL ====== */
        .ci-wrap {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* ====== BARRA DE BÚSQUEDA ====== */
        .ci-search-box {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #fff;
            border: 1px solid #d0d7e2;
            border-radius: 50px;
            padding: .6rem 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
            transition: border-color .2s, box-shadow .2s;
        }

        .ci-search-box:focus-within {
            border-color: #1b3a63;
            box-shadow: 0 0 0 3px rgba(27, 58, 99, 0.15);
        }

        .ci-search-box i.fa-magnifying-glass {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .ci-search-box input {
            flex: 1;
            border: 0;
            outline: none;
            font-size: 1rem;
            background: transparent;
            padding: .4rem 0;
        }

        .ci-search-box input::placeholder {
            color: #a0aec0;
        }

        /* ====== BOTÓN BUSCAR ====== */
        .ci-buscar-wrap {
            text-align: center;
            margin: 1.25rem 0 1.75rem;
        }

        .ci-btn-buscar {
            background: #1b3a63;
            color: #fff;
            border: 0;
            border-radius: 50px;
            padding: .7rem 3rem;
            font-weight: 700;
            letter-spacing: .04em;
            font-size: .9rem;
            cursor: pointer;
            transition: background .15s, transform .1s;
            box-shadow: 0 3px 8px rgba(27, 58, 99, 0.25);
        }

        .ci-btn-buscar:hover {
            background: #142c4d;
            transform: translateY(-1px);
        }

        .ci-btn-buscar:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* ====== ESTADOS VACÍO / ERROR ====== */
        .ci-empty {
            text-align: center;
            color: #6c757d;
            padding: 2.5rem 1rem;
            background: #f8fafc;
            border-radius: .75rem;
            border: 1px dashed #d0d7e2;
        }

        .ci-empty i {
            font-size: 2.4rem;
            margin-bottom: .75rem;
            display: block;
            color: #a0aec0;
        }

        .ci-error {
            color: #b02a37;
            border-color: #f5c2c7;
            background: #fff5f5;
        }

        .ci-error i {
            color: #dc3545;
        }

        /* ====== TARJETA DE RESULTADOS ====== */
        .ci-card {
            background: #fff;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #e9edf4;
        }

        .ci-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.15rem 1.5rem;
            border-bottom: 1px solid #e9edf4;
            background: #f8fafc;
            flex-wrap: wrap;
        }

        .ci-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #1b3a63;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .ci-nombre-wrap {
            flex: 1;
            min-width: 150px;
        }

        .ci-nombre {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
            color: #0b1e33;
        }

        .ci-conteo {
            color: #5a6a7e;
            font-size: .85rem;
            font-weight: 500;
        }

        .ci-badge-docs {
            background: #eef2fd;
            color: #1b3a63;
            font-weight: 600;
            padding: .25rem 1rem;
            border-radius: 50px;
            font-size: .8rem;
            white-space: nowrap;
        }

        /* ====== TABLA ====== */
        .ci-card-body {
            padding: .25rem 0 .25rem;
        }

        .ci-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: .92rem;
        }

        .ci-tabla thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e9edf4;
            padding: .85rem 1.25rem;
            font-weight: 600;
            color: #1e2f44;
            text-transform: uppercase;
            font-size: .72rem;
            letter-spacing: .03em;
        }

        .ci-tabla tbody td {
            padding: .85rem 1.25rem;
            border-bottom: 1px solid #edf1f8;
            vertical-align: middle;
        }

        .ci-tabla tbody tr:last-child td {
            border-bottom: 0;
        }

        .ci-tabla tbody tr:hover {
            background-color: #f8fafc;
        }

        .ci-fecha i {
            color: #8a9aa8;
            margin-right: .5rem;
            width: 1rem;
            text-align: center;
        }

        /* ====== BOTÓN "VER IPS" ====== */
        .ci-btn-ver {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
            color: #1b3a63;
            border: 1.5px solid #1b3a63;
            border-radius: 30px;
            padding: .35rem 1.2rem;
            font-weight: 600;
            font-size: .78rem;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s;
            letter-spacing: .02em;
        }

        .ci-btn-ver i {
            font-size: .8rem;
        }

        .ci-btn-ver:hover {
            background: #1b3a63;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(27, 58, 99, 0.3);
        }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 576px) {
            .ci-search-box {
                padding: .5rem 1rem;
                border-radius: 30px;
            }

            .ci-btn-buscar {
                padding: .6rem 2rem;
                font-size: .8rem;
                width: 100%;
            }

            .ci-card-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .ci-avatar {
                margin: 0 auto;
            }

            .ci-tabla thead {
                display: none;
            }

            .ci-tabla tbody td {
                display: block;
                padding: .6rem 1rem;
                border-bottom: 0;
            }

            .ci-tabla tbody tr {
                display: block;
                border-bottom: 2px solid #e9edf4;
                padding: .5rem 0;
            }

            .ci-tabla tbody td:before {
                content: attr(data-label);
                font-weight: 600;
                display: inline-block;
                width: 100px;
                color: #2d4056;
            }

            .ci-tabla tbody td:last-child {
                border-bottom: 0;
            }
        }
    </style>
@stop

@section('content')
    <div class="ci-wrap">
        {{-- FORMULARIO DE BÚSQUEDA --}}
        <form method="GET" action="{{ route('consulta-ips') }}" id="ci-form">
            <div class="ci-search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" name="identificador" id="ci-input"
                       placeholder="Ingresa número de identificación, nombre o ID del paciente"
                       value="{{ $identificador ?? '' }}" autocomplete="off">
            </div>

            <div class="ci-buscar-wrap">
                <button type="submit" class="ci-btn-buscar" id="ci-boton">
                    <i class="fas fa-search"></i> BUSCAR
                </button>
            </div>
        </form>

        {{-- CONTENEDOR DE RESULTADOS --}}
        <div id="ci-resultados">
            @if(isset($error) && $error)
                <div class="ci-empty ci-error">
                    <i class="fas fa-triangle-exclamation"></i>
                    <p>{{ $error }}</p>
                </div>
            @elseif(isset($documentos) && $documentos !== null)
                @include('consulta-ips._resultados', [
                    'documentos' => $documentos,
                    'nombrePaciente' => $nombrePaciente ?? 'Paciente'
                ])
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

            // ====== HELPER: HTML PARA ERROR ======
            function estadoErrorResultados(mensaje) {
                return `<div class="ci-empty ci-error">
                            <i class="fas fa-triangle-exclamation"></i>
                            <p>${mensaje}</p>
                        </div>`;
            }

            // ====== INICIALIZAR DATATABLE ======
            function inicializarTabla() {
                const tabla = document.getElementById('ci-tabla');
                if (tabla && window.jQuery) {
                    // Destruir instancia anterior si existe
                    if ($.fn.DataTable.isDataTable(tabla)) {
                        $(tabla).DataTable().destroy();
                    }

                    $(tabla).DataTable({
                        pageLength: 10,
                        lengthChange: false,
                        searching: false,
                        order: [[1, 'desc']],
                        language: {
                            zeroRecords: 'No se encontraron documentos',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ documentos',
                            infoEmpty: 'No hay documentos disponibles',
                            infoFiltered: '(filtrado de _MAX_ total)',
                            paginate: {
                                previous: '<i class="fas fa-chevron-left"></i>',
                                next: '<i class="fas fa-chevron-right"></i>'
                            }
                        },
                        drawCallback: function () {
                            // Asegurar que los botones tengan el estilo correcto después del redibujo
                            $('.ci-btn-ver').each(function () {
                                $(this).addClass('ci-btn-ver');
                            });
                        }
                    });
                }
            }

            // ====== BÚSQUEDA ASÍNCRONA ======
            async function buscar(identificador) {
                boton.disabled = true;
                resultados.innerHTML = `<div class="ci-empty">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <p>Buscando documentos...</p>
                                        </div>`;

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

                    if (response.ok && data.html) {
                        resultados.innerHTML = data.html;
                        inicializarTabla();
                    } else {
                        resultados.innerHTML = estadoErrorResultados(
                            data.error ?? 'No se encontraron resultados para este identificador.'
                        );
                    }
                } catch (e) {
                    resultados.innerHTML = estadoErrorResultados(
                        'Error de conexión. Verifica tu red e intenta nuevamente.'
                    );
                } finally {
                    boton.disabled = false;
                }
            }

            // ====== EVENTO SUBMIT ======
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const identificador = input.value.trim();
                if (identificador.length > 0) {
                    buscar(identificador);
                } else {
                    resultados.innerHTML = estadoErrorResultados('Por favor, ingresa un identificador válido.');
                }
            });

            // ====== BÚSQUEDA CON TECLA ENTER (fallback) ======
            input.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.dispatchEvent(new Event('submit'));
                }
            });

            // ====== INICIALIZAR TABLA SI YA HAY DATOS ======
            if (document.getElementById('ci-tabla')) {
                inicializarTabla();
            }
        })();
    </script>
@stop
