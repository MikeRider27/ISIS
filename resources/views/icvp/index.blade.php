@extends('adminlte::page')

@section('title', 'ICVP')

@section('content_header')
    <div style="text-align: center; width: 100%;">
        <h1 style="display: inline-block;">
            <i class="fas fa-syringe"></i> ICVP
        </h1>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
    <style>
        .icvp-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .icvp-campo-id input {
            width: 100%;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem 1.1rem;
            font-size: 1rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .icvp-campo-id input:focus {
            outline: none;
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15);
        }

        .icvp-buscar-wrap {
            text-align: center;
            margin: 1.5rem 0;
        }

        .icvp-btn-generar {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #1b3a63;
            color: #fff;
            border: 0;
            border-radius: 2rem;
            padding: .65rem 2.5rem;
            font-weight: 700;
            letter-spacing: .03em;
            font-size: .85rem;
            cursor: pointer;
            transition: background .15s, transform .1s, box-shadow .15s;
            box-shadow: 0 3px 8px rgba(27, 58, 99, 0.2);
        }

        .icvp-btn-generar:hover {
            background: #142c4d;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(27, 58, 99, 0.3);
        }

        .icvp-btn-generar:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .visor-empty {
            text-align: center;
            color: #6c757d;
            padding: 2.5rem 1rem;
            background: #f8fafc;
            border-radius: .75rem;
            border: 1px dashed #d0d7e2;
        }

        .visor-empty-icon {
            font-size: 2.4rem;
            margin-bottom: .75rem;
            display: block;
            color: #a0aec0;
        }

        .visor-error {
            color: #b02a37;
            border-color: #f5c2c7;
            background: #fff5f5;
        }

        .visor-error .visor-empty-icon {
            color: #dc3545;
        }

        .icvp-paciente {
            font-weight: 700;
            font-size: 1.2rem;
            color: #0b1e33;
            text-align: center;
            margin: 0 0 1.25rem;
        }

        .icvp-tabla-card {
            background: #fff;
            border-radius: .75rem;
            border: 1px solid #e9edf4;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .icvp-tabla td {
            vertical-align: middle;
        }

        .icvp-tabla-codigo {
            font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .8rem;
            color: #495057;
        }

        .icvp-qr-celda {
            text-align: center;
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .icvp-qr-thumb {
            border: 1px solid #dee2e6;
            border-radius: .4rem;
            padding: .4rem;
            background: #fff;
            width: 110px;
            height: 110px;
        }

        .icvp-qr-acciones {
            display: flex;
            justify-content: center;
            gap: .5rem;
            margin-top: .5rem;
        }

        .icvp-copiar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: transparent;
            border: 1.5px solid #1b3a63;
            color: #1b3a63;
            border-radius: 50%;
            font-size: .8rem;
            text-decoration: none;
            cursor: pointer;
            transition: all .15s;
        }

        .icvp-copiar:hover {
            background: #1b3a63;
            color: #fff;
            text-decoration: none;
        }

        @media (max-width: 576px) {
            .icvp-btn-generar {
                width: 100%;
                justify-content: center;
            }

            .icvp-qr-thumb {
                width: 90px;
                height: 90px;
            }
        }
    </style>
@stop

@section('content')
    <div class="icvp-wrap">
        <form id="icvp-form">
            <div class="icvp-campo-id">
                <input type="text" id="icvp-bundle-id" placeholder="ID del Bundle (ej. 2034)" autocomplete="off">
            </div>

            <div class="icvp-buscar-wrap">
                <button type="submit" id="icvp-boton-generar" class="icvp-btn-generar">
                    <i class="fas fa-qrcode"></i> GENERAR QR ICVP
                </button>
            </div>
        </form>

        <div id="icvp-resultado">
            <div class="visor-empty">
                <div class="visor-empty-icon"><i class="fas fa-syringe"></i></div>
                <p>Ingresá el ID de un Bundle ICVP ya publicado en el servidor FHIR del MSPBS.</p>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('icvp-form');
            const bundleIdInput = document.getElementById('icvp-bundle-id');
            const boton = document.getElementById('icvp-boton-generar');
            const resultado = document.getElementById('icvp-resultado');

            const urlGenerar = @json(route('icvp.generar'));
            const token = @json(csrf_token());

            function escapeHtml(texto) {
                const div = document.createElement('div');
                div.textContent = texto;
                return div.innerHTML;
            }

            function estadoVacio(mensaje) {
                return `<div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-syringe"></i></div>
                    <p>${mensaje}</p>
                </div>`;
            }

            function estadoError(mensaje) {
                return `<div class="visor-empty visor-error">
                    <div class="visor-empty-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <p>${mensaje}</p>
                </div>`;
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const bundleId = bundleIdInput.value.trim();
                if (!bundleId) {
                    return;
                }

                boton.disabled = true;
                resultado.innerHTML = estadoVacio('Generando…');

                try {
                    const response = await fetch(urlGenerar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: new URLSearchParams({ bundle_id: bundleId }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        const certificados = data.certificados || [];
                        const nombre = data.nombrePaciente
                            ? `<p class="icvp-paciente">${escapeHtml(data.nombrePaciente)}</p>`
                            : '';

                        const filas = certificados.map(function (cert, i) {
                            const nombreArchivo = certificados.length > 1
                                ? `icvp-${bundleId}-${i + 1}.png`
                                : `icvp-${bundleId}.png`;

                            return `<tr>
                                <td class="icvp-tabla-codigo">${escapeHtml(cert.vacunaCodigo || '—')}</td>
                                <td>${escapeHtml(cert.vacunaNombre || 'Vacuna')}</td>
                                <td class="icvp-qr-celda">
                                    <img src="${cert.qr}" alt="Código QR ICVP" class="icvp-qr-thumb">
                                    <div class="icvp-qr-acciones">
                                        <a href="${cert.qr}" download="${nombreArchivo}" class="icvp-copiar" title="Descargar QR">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="icvp-copiar icvp-btn-copiar" data-codigo="${escapeHtml(cert.codigo)}" title="Copiar código HC1">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                        }).join('');

                        resultado.innerHTML = `${nombre}
                            <div class="icvp-tabla-card">
                                <table id="icvp-tabla" class="table table-hover icvp-tabla" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Vacuna</th>
                                            <th>QR</th>
                                        </tr>
                                    </thead>
                                    <tbody>${filas}</tbody>
                                </table>
                            </div>`;

                        resultado.querySelectorAll('.icvp-btn-copiar').forEach(function (boton) {
                            boton.addEventListener('click', function () {
                                navigator.clipboard.writeText(this.dataset.codigo);
                                const icono = this.querySelector('i');
                                icono.classList.remove('fa-copy');
                                icono.classList.add('fa-check');
                                setTimeout(function () {
                                    icono.classList.remove('fa-check');
                                    icono.classList.add('fa-copy');
                                }, 1500);
                            });
                        });

                        const tabla = document.getElementById('icvp-tabla');
                        if (tabla && window.jQuery) {
                            if ($.fn.DataTable.isDataTable(tabla)) {
                                $(tabla).DataTable().destroy();
                            }

                            $(tabla).DataTable({
                                pageLength: 10,
                                lengthChange: false,
                                searching: false,
                                paging: certificados.length > 10,
                                info: certificados.length > 10,
                                columnDefs: [{ orderable: false, targets: 2 }],
                                language: {
                                    zeroRecords: 'No se encontraron vacunas',
                                    info: 'Mostrando _START_ a _END_ de _TOTAL_ vacunas',
                                    paginate: {
                                        previous: '<i class="fas fa-chevron-left"></i>',
                                        next: '<i class="fas fa-chevron-right"></i>',
                                    },
                                },
                            });
                        }
                    } else {
                        resultado.innerHTML = estadoError(data.error ?? 'No se pudo generar el QR ICVP.');
                    }
                } catch (e) {
                    resultado.innerHTML = estadoError('No se pudo conectar con el servidor.');
                } finally {
                    boton.disabled = false;
                }
            });
        })();
    </script>
@stop
