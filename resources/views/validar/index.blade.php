@extends('adminlte::page')

@section('title', 'Validar QR')

@section('content_header')
    <div style="text-align: center; width: 100%;">
        <h1 style="display: inline-block;">
            <i class="fas fa-shield-halved"></i> Validar QR (ICVP / MEOW)
        </h1>
    </div>
@stop

@section('css')
    <style>
        .val-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .val-campos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .val-file-label {
            display: flex;
            align-items: center;
            gap: .6rem;
            height: 100%;
            min-height: 60px;
            background: #fff;
            border: 2px dashed #adb5bd;
            border-radius: .5rem;
            padding: .75rem 1.1rem;
            font-size: .9rem;
            color: #6c757d;
            cursor: pointer;
            margin: 0;
            transition: all .15s;
        }

        .val-file-label:hover {
            border-color: #4e73df;
            color: #4e73df;
        }

        .val-file-label.is-arrastrando {
            border-style: solid;
            border-color: #4e73df;
            background: #eef2fd;
            color: #4e73df;
        }

        .val-file-label.is-listo {
            border-style: solid;
            border-color: #198754;
            color: #198754;
            background: #f0fff4;
        }

        .val-file-label.is-error {
            border-style: solid;
            border-color: #dc3545;
            color: #dc3545;
            background: #fff5f5;
        }

        .val-camara-toggle {
            text-align: center;
            margin: -.75rem 0 1.5rem;
        }

        .val-link-camara {
            background: transparent;
            border: 0;
            color: #1b3a63;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
            padding: .25rem;
        }

        .val-link-camara:hover {
            color: #0d2a4a;
        }

        .val-camara-panel {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .val-camara-panel video {
            max-width: 100%;
            width: 360px;
            border-radius: .5rem;
            border: 1px solid #dee2e6;
            background: #000;
        }

        .val-camara-acciones {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: .75rem;
            font-size: .85rem;
            color: #6c757d;
            flex-wrap: wrap;
        }

        .val-buscar-wrap {
            text-align: center;
            margin: 1.5rem 0;
        }

        .val-btn-validar {
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

        .val-btn-validar:hover {
            background: #142c4d;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(27, 58, 99, 0.3);
        }

        .val-btn-validar:disabled {
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

        .val-banner {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: 1rem 1.25rem;
            border-radius: .6rem;
            margin-bottom: 1.25rem;
            font-weight: 600;
        }

        .val-banner i {
            font-size: 1.6rem;
        }

        .val-banner.is-valido {
            background: #f0fff4;
            border: 1px solid #a8dfc0;
            color: #157347;
        }

        .val-banner.is-invalido {
            background: #fff5f5;
            border: 1px solid #f5c2c7;
            color: #b02a37;
        }

        .val-tipo-badge {
            display: inline-block;
            background: #eef2fd;
            color: #4e73df;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: .25rem;
            padding: .2rem .55rem;
            margin-left: .5rem;
            vertical-align: middle;
        }

        .val-paciente-card {
            background: #fff;
            border-radius: .75rem;
            border: 1px solid #e9edf4;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .val-paciente-nombre {
            font-weight: 700;
            font-size: 1.15rem;
            color: #0b1e33;
            margin-bottom: .5rem;
        }

        .val-paciente-datos {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem 1.5rem;
            font-size: .85rem;
            color: #495057;
        }

        .val-paciente-datos strong {
            color: #6c757d;
            font-weight: 600;
        }

        .val-tabla-card {
            background: #fff;
            border-radius: .75rem;
            border: 1px solid #e9edf4;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 1.25rem 1.5rem 1.5rem;
        }

        .val-tabla th, .val-tabla td {
            vertical-align: middle;
        }

        .val-tabla-codigo {
            font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .8rem;
            color: #495057;
        }

        @media (max-width: 767px) {
            .val-campos {
                grid-template-columns: 1fr;
            }
        }
    </style>
@stop

@section('content')
    <div class="val-wrap">
        <form id="val-form">
            <div class="val-campos">
                <label for="val-qr-file" class="val-file-label" id="val-file-label">
                    <i class="fas fa-upload"></i>
                    <span id="val-file-nombre">Subir o arrastrar imagen del QR</span>
                </label>
                <input type="file" id="val-qr-file" accept="image/*" style="display: none;">
            </div>

            <div class="val-camara-toggle">
                <button type="button" id="val-btn-camara" class="val-link-camara">
                    <i class="fas fa-camera"></i> Usar la cámara de la PC
                </button>
            </div>

            <div id="val-camara-panel" class="val-camara-panel" style="display: none;">
                <video id="val-camara-video" autoplay playsinline muted></video>
                <div class="val-camara-acciones">
                    <span id="val-camara-estado">Buscando código QR…</span>
                    <button type="button" id="val-btn-camara-cancelar" class="val-link-camara">
                        <i class="fas fa-xmark"></i> Cancelar
                    </button>
                </div>
            </div>

            <div class="val-buscar-wrap">
                <button type="submit" id="val-boton-validar" class="val-btn-validar" disabled>
                    <i class="fas fa-shield-halved"></i> VALIDAR
                </button>
            </div>
        </form>

        <div id="val-resultado">
            <div class="visor-empty">
                <div class="visor-empty-icon"><i class="fas fa-qrcode"></i></div>
                <p>Subí una imagen o usá la cámara para escanear un QR de ICVP o MEOW.</p>
            </div>
        </div>

        <canvas id="val-qr-canvas" style="display: none;"></canvas>
    </div>
@stop

@section('js')
    <script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
    <script>
        (function () {
            const form = document.getElementById('val-form');
            const qrFileInput = document.getElementById('val-qr-file');
            const fileLabel = document.getElementById('val-file-label');
            const fileNombre = document.getElementById('val-file-nombre');
            const boton = document.getElementById('val-boton-validar');
            const resultado = document.getElementById('val-resultado');
            const canvas = document.getElementById('val-qr-canvas');
            let qrDecodificado = null;

            const btnCamara = document.getElementById('val-btn-camara');
            const camaraPanel = document.getElementById('val-camara-panel');
            const camaraVideo = document.getElementById('val-camara-video');
            const camaraEstado = document.getElementById('val-camara-estado');
            const btnCamaraCancelar = document.getElementById('val-btn-camara-cancelar');
            let camaraStream = null;
            let camaraFrameId = null;

            const urlVerificar = @json(route('validar.verificar'));
            const token = @json(csrf_token());

            function escapeHtml(texto) {
                const div = document.createElement('div');
                div.textContent = texto ?? '';
                return div.innerHTML;
            }

            function estadoVacio(mensaje) {
                return `<div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-qrcode"></i></div>
                    <p>${mensaje}</p>
                </div>`;
            }

            function estadoError(mensaje) {
                return `<div class="visor-empty visor-error">
                    <div class="visor-empty-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <p>${mensaje}</p>
                </div>`;
            }

            function procesarArchivo(file) {
                qrDecodificado = null;
                boton.disabled = true;
                fileLabel.classList.remove('is-listo', 'is-error');

                if (!file) {
                    fileNombre.textContent = 'Subir o arrastrar imagen del QR';
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    fileNombre.textContent = 'Ese archivo no es una imagen.';
                    fileLabel.classList.add('is-error');
                    return;
                }

                fileNombre.textContent = 'Leyendo…';

                const img = new Image();
                const objectUrl = URL.createObjectURL(file);

                img.onload = function () {
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const leido = window.jsQR ? jsQR(imageData.data, imageData.width, imageData.height) : null;

                    if (leido && leido.data && leido.data.startsWith('HC1:')) {
                        qrDecodificado = leido.data;
                        fileNombre.textContent = file.name + ' — QR detectado';
                        fileLabel.classList.add('is-listo');
                        boton.disabled = false;
                    } else {
                        fileNombre.textContent = 'No se pudo leer un código HC1 en esa imagen.';
                        fileLabel.classList.add('is-error');
                    }

                    URL.revokeObjectURL(objectUrl);
                };

                img.onerror = function () {
                    fileNombre.textContent = 'No se pudo abrir la imagen.';
                    fileLabel.classList.add('is-error');
                    URL.revokeObjectURL(objectUrl);
                };

                img.src = objectUrl;
            }

            qrFileInput.addEventListener('change', function () {
                procesarArchivo(this.files[0]);
            });

            ['dragenter', 'dragover'].forEach(function (evento) {
                fileLabel.addEventListener(evento, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileLabel.classList.add('is-arrastrando');
                });
            });

            ['dragleave', 'dragend'].forEach(function (evento) {
                fileLabel.addEventListener(evento, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileLabel.classList.remove('is-arrastrando');
                });
            });

            fileLabel.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                fileLabel.classList.remove('is-arrastrando');

                const file = e.dataTransfer.files && e.dataTransfer.files[0];
                if (file) {
                    qrFileInput.value = '';
                    procesarArchivo(file);
                }
            });

            async function iniciarCamara() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    camaraEstado.textContent = 'Este navegador no permite acceder a la cámara.';
                    return;
                }

                try {
                    camaraStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment' },
                    });
                } catch (e) {
                    camaraEstado.textContent = 'No se pudo acceder a la cámara. Revisá los permisos del navegador.';
                    return;
                }

                camaraVideo.srcObject = camaraStream;
                camaraPanel.style.display = '';
                btnCamara.style.display = 'none';
                camaraEstado.textContent = 'Buscando código QR…';
                qrDecodificado = null;
                boton.disabled = true;
                fileLabel.classList.remove('is-listo', 'is-error');
                fileNombre.textContent = 'Subir o arrastrar imagen del QR';

                escanearFrame();
            }

            function detenerCamara() {
                if (camaraFrameId) {
                    cancelAnimationFrame(camaraFrameId);
                    camaraFrameId = null;
                }

                if (camaraStream) {
                    camaraStream.getTracks().forEach(function (track) {
                        track.stop();
                    });
                    camaraStream = null;
                }

                camaraVideo.srcObject = null;
                camaraPanel.style.display = 'none';
                btnCamara.style.display = '';
            }

            function escanearFrame() {
                if (!camaraStream) return;

                if (camaraVideo.readyState === camaraVideo.HAVE_ENOUGH_DATA) {
                    canvas.width = camaraVideo.videoWidth;
                    canvas.height = camaraVideo.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(camaraVideo, 0, 0, canvas.width, canvas.height);

                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const leido = window.jsQR ? jsQR(imageData.data, imageData.width, imageData.height) : null;

                    if (leido && leido.data && leido.data.startsWith('HC1:')) {
                        qrDecodificado = leido.data;
                        detenerCamara();
                        fileNombre.textContent = 'QR detectado con la cámara';
                        fileLabel.classList.add('is-listo');
                        boton.disabled = false;
                        return;
                    }
                }

                camaraFrameId = requestAnimationFrame(escanearFrame);
            }

            btnCamara.addEventListener('click', iniciarCamara);
            btnCamaraCancelar.addEventListener('click', detenerCamara);

            function renderResultado(data) {
                const banner = `<div class="val-banner ${data.valido ? 'is-valido' : 'is-invalido'}">
                    <i class="fas ${data.valido ? 'fa-circle-check' : 'fa-circle-xmark'}"></i>
                    <div>
                        ${data.valido ? 'Firma válida' : 'Firma inválida'}
                        <span class="val-tipo-badge">${escapeHtml((data.tipo || 'desconocido').toUpperCase())}</span>
                        <div style="font-weight: 400; font-size: .82rem; margin-top: .2rem;">${escapeHtml(data.mensajeFirma)}</div>
                    </div>
                </div>`;

                const p = data.paciente || {};
                const paciente = `<div class="val-paciente-card">
                    <div class="val-paciente-nombre">${escapeHtml(p.nombre || 'Paciente')}</div>
                    <div class="val-paciente-datos">
                        <span><strong>Nacimiento:</strong> ${escapeHtml(p.nacimiento || '—')}</span>
                        <span><strong>Sexo:</strong> ${escapeHtml(p.sexo || '—')}</span>
                        <span><strong>Identificador:</strong> ${escapeHtml(p.identificador || '—')} ${p.identificadorTipo ? '(' + escapeHtml(p.identificadorTipo) + ')' : ''}</span>
                        <span><strong>País:</strong> ${escapeHtml(data.pais || '—')}</span>
                    </div>
                </div>`;

                const items = data.items || [];
                let tabla;

                if (data.tipo === 'meow') {
                    const filas = items.map((item) => `<tr>
                        <td class="val-tabla-codigo">${escapeHtml(item.codigo || '—')}</td>
                        <td>${escapeHtml(item.nombre || 'Medicamento')}</td>
                        <td>${escapeHtml(item.fecha || '—')}</td>
                        <td>${escapeHtml(item.dosis || '—')}</td>
                    </tr>`).join('');

                    tabla = `<div class="val-tabla-card">
                        <table class="table table-hover val-tabla" style="width: 100%">
                            <thead><tr><th>Código</th><th>Medicamento</th><th>Fecha</th><th>Dosis</th></tr></thead>
                            <tbody>${filas || '<tr><td colspan="4" class="text-center text-muted">Sin medicamentos</td></tr>'}</tbody>
                        </table>
                    </div>`;
                } else if (data.tipo === 'icvp') {
                    const filas = items.map((item) => `<tr>
                        <td class="val-tabla-codigo">${escapeHtml(item.codigo || '—')}</td>
                        <td>${escapeHtml(item.fecha || '—')}</td>
                        <td>${escapeHtml(item.lote || '—')}</td>
                    </tr>`).join('');

                    tabla = `<div class="val-tabla-card">
                        <table class="table table-hover val-tabla" style="width: 100%">
                            <thead><tr><th>Código</th><th>Fecha</th><th>Lote</th></tr></thead>
                            <tbody>${filas || '<tr><td colspan="3" class="text-center text-muted">Sin vacunas</td></tr>'}</tbody>
                        </table>
                    </div>`;
                } else {
                    tabla = `<div class="val-tabla-card">${estadoVacio('No se reconoció el tipo de certificado.')}</div>`;
                }

                return banner + paciente + tabla;
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (!qrDecodificado) return;

                boton.disabled = true;
                resultado.innerHTML = estadoVacio('Validando…');

                try {
                    const response = await fetch(urlVerificar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: new URLSearchParams({ qr_code_content: qrDecodificado }),
                    });

                    const data = await response.json();

                    resultado.innerHTML = response.ok
                        ? renderResultado(data)
                        : estadoError(data.error ?? 'No se pudo validar el código.');
                } catch (e) {
                    resultado.innerHTML = estadoError('No se pudo conectar con el servidor.');
                } finally {
                    boton.disabled = false;
                }
            });
        })();
    </script>
@stop
