@extends('adminlte::page')

@section('title', 'VHL')

@section('content_header')
    <h1>VHL - Enlace de Salud Virtual</h1>
@stop

@section('css')
    <style>
        .vhl-wrap {
            max-width: 900px;
            margin: 0 auto;
        }

        .vhl-toggle {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.75rem;
        }

        .vhl-toggle-btn {
            background: #1b3a63;
            color: #fff;
            border: 0;
            border-radius: .35rem;
            padding: .55rem 1.5rem;
            font-weight: 700;
            font-size: .8rem;
            letter-spacing: .03em;
            cursor: pointer;
        }

        .vhl-toggle-btn.is-inactive {
            background: transparent;
            color: #1b3a63;
            text-decoration: underline;
            font-weight: 600;
            padding: .55rem .25rem;
        }

        .vhl-campos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .vhl-campo-id input {
            width: 100%;
            height: 100%;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem 1.1rem;
            font-size: 1rem;
        }

        .vhl-campo-id input:focus {
            outline: none;
            border-color: #4e73df;
        }

        .vhl-fieldset {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: 0 .9rem .5rem;
            margin: 0;
        }

        .vhl-fieldset legend {
            width: auto;
            padding: 0 .4rem;
            margin: 0 0 0 .35rem;
            font-size: .72rem;
            color: #6c757d;
            float: none;
        }

        .vhl-fieldset select {
            border: 0;
            outline: none;
            width: 100%;
            background: transparent;
            font-size: .95rem;
            padding: .2rem 0;
        }

        .vhl-buscar-wrap {
            text-align: center;
            margin: 1.5rem 0;
        }

        .vhl-btn-buscar {
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
        }

        .vhl-btn-buscar:hover {
            background: #142c4d;
            color: #fff;
        }

        .vhl-btn-buscar:disabled {
            opacity: .6;
            cursor: default;
        }

        .vhl-subtitulo {
            color: #495057;
            font-weight: 600;
            margin-bottom: .75rem;
        }

        .vhl-lista-recursos {
            display: flex;
            flex-direction: column;
            gap: .5rem;
            margin-bottom: 1.5rem;
            max-height: 320px;
            overflow-y: auto;
        }

        .vhl-recurso {
            display: flex;
            align-items: center;
            gap: .75rem;
            background: #f8f9fc;
            border: 1px solid #e9ecef;
            border-radius: .4rem;
            padding: .6rem .9rem;
            margin: 0;
            cursor: pointer;
        }

        .vhl-recurso input {
            flex-shrink: 0;
        }

        .vhl-recurso-tipo {
            background: #eef2fd;
            color: #4e73df;
            font-size: .7rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: .25rem;
            padding: .15rem .5rem;
            flex-shrink: 0;
        }

        .vhl-recurso-texto {
            color: #212529;
            font-size: .9rem;
        }

        .vhl-campos-emision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: .5rem;
        }

        .vhl-campos-emision label {
            display: block;
            font-size: .8rem;
            color: #6c757d;
            margin-bottom: .3rem;
        }

        .vhl-campos-emision input {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: .35rem;
            padding: .5rem .75rem;
        }

        .vhl-qr-card {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .vhl-qr-card img {
            border: 1px solid #dee2e6;
            border-radius: .5rem;
            padding: .75rem;
            background: #fff;
        }

        .vhl-codigo {
            max-width: 520px;
            margin: 1rem auto 0;
            word-break: break-all;
            background: #f8f9fc;
            border: 1px solid #e9ecef;
            border-radius: .4rem;
            padding: .75rem;
            font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .75rem;
            color: #495057;
            text-align: left;
        }

        .vhl-acciones-qr {
            display: flex;
            justify-content: center;
            gap: .75rem;
            margin-top: .75rem;
        }

        .vhl-copiar {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: transparent;
            border: 1px solid #1b3a63;
            color: #1b3a63;
            border-radius: .35rem;
            padding: .4rem 1rem;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .vhl-copiar:hover {
            background: #1b3a63;
            color: #fff;
        }

        .vhl-file-label {
            display: flex;
            align-items: center;
            gap: .6rem;
            height: 100%;
            background: #fff;
            border: 1px dashed #adb5bd;
            border-radius: .5rem;
            padding: .75rem 1.1rem;
            font-size: .9rem;
            color: #6c757d;
            cursor: pointer;
            margin: 0;
            transition: border-color .15s, background-color .15s, color .15s;
        }

        .vhl-file-label:hover {
            border-color: #4e73df;
            color: #4e73df;
        }

        .vhl-file-label.is-arrastrando {
            border-style: solid;
            border-color: #4e73df;
            background: #eef2fd;
            color: #4e73df;
        }

        .vhl-file-label.is-listo {
            border-style: solid;
            border-color: #198754;
            color: #198754;
        }

        .vhl-file-label.is-error {
            border-style: solid;
            border-color: #dc3545;
            color: #dc3545;
        }

        .vhl-pasos {
            list-style: none;
            padding: 0;
            margin: 0 0 1rem;
        }

        .vhl-paso {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .55rem 0;
            border-bottom: 1px solid #e9ecef;
            font-size: .88rem;
        }

        .vhl-paso:last-child {
            border-bottom: 0;
        }

        .vhl-paso--ok i {
            color: #198754;
        }

        .vhl-paso--error i {
            color: #dc3545;
        }

        .vhl-manifest {
            background: #f8f9fc;
            border: 1px solid #e9ecef;
            border-radius: .4rem;
            padding: .9rem 1.1rem;
            font-size: .88rem;
            color: #495057;
        }

        .vhl-manifest p {
            margin: 0 0 .35rem;
        }

        .vhl-manifest p:last-child {
            margin-bottom: 0;
        }

        .vhl-split {
            display: flex;
            align-items: stretch;
            gap: 1rem;
            min-height: 320px;
        }

        .vhl-pasos-panel {
            padding: 1.5rem;
            flex: 1;
        }

        .vhl-descargar-json {
            margin-top: 1.25rem;
        }

        .vhl-camara-toggle {
            text-align: center;
            margin: -.75rem 0 1.5rem;
        }

        .vhl-link-camara {
            background: transparent;
            border: 0;
            color: #1b3a63;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
            padding: .25rem;
        }

        .vhl-camara-panel {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .vhl-camara-panel video {
            max-width: 100%;
            width: 360px;
            border-radius: .5rem;
            border: 1px solid #dee2e6;
            background: #000;
        }

        .vhl-camara-acciones {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: .75rem;
            font-size: .85rem;
            color: #6c757d;
        }

        @media (max-width: 767px) {
            .vhl-split {
                flex-direction: column;
            }
        }
    </style>
    @include('partials.ficha-styles')
@stop

@section('content')
    <div class="vhl-wrap">
        <div class="vhl-toggle">
            <button type="button" class="vhl-toggle-btn" id="vhl-tab-generar" data-modo="generar">GENERAR VHL</button>
            <button type="button" class="vhl-toggle-btn is-inactive" id="vhl-tab-ver" data-modo="ver">VER VHL</button>
        </div>

        <div id="vhl-panel-generar">
            <form id="vhl-form-buscar">
                <div class="vhl-campos">
                    <div class="vhl-campo-id">
                        <input type="text" id="vhl-bundle-id" placeholder="ID del Bundle" autocomplete="off">
                    </div>

                    <fieldset class="vhl-fieldset">
                        <legend>Servidor FHIR</legend>
                        <select id="vhl-servidor">
                            @foreach($servidores as $url => $nombre)
                                <option value="{{ $url }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </fieldset>
                </div>

                <div class="vhl-buscar-wrap">
                    <button type="submit" id="vhl-boton-buscar" class="vhl-btn-buscar">BUSCAR</button>
                </div>
            </form>

            <div id="vhl-resultado">
                <div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-qrcode"></i></div>
                    <p>No se han cargado datos.</p>
                </div>
            </div>
        </div>

        <div id="vhl-panel-ver" style="display: none;">
            <form id="vhl-form-validar">
                <div class="vhl-campos">
                    <label for="vhl-qr-file" class="vhl-file-label" id="vhl-file-label">
                        <i class="fas fa-upload"></i>
                        <span id="vhl-file-nombre">Subir o arrastrar imagen del QR</span>
                    </label>
                    <input type="file" id="vhl-qr-file" accept="image/*" style="display: none;">

                    <fieldset class="vhl-fieldset">
                        <legend>Código de acceso (PIN)</legend>
                        <input type="text" id="vhl-validar-pass-code" inputmode="numeric" pattern="\d{4,8}"
                            placeholder="1234" maxlength="8" style="border: 0; outline: none; width: 100%; background: transparent; padding: .3rem 0;">
                    </fieldset>
                </div>

                <div class="vhl-camara-toggle">
                    <button type="button" id="vhl-btn-camara" class="vhl-link-camara">
                        <i class="fas fa-camera"></i> Usar la cámara de la PC
                    </button>
                </div>

                <div id="vhl-camara-panel" class="vhl-camara-panel" style="display: none;">
                    <video id="vhl-camara-video" autoplay playsinline muted></video>
                    <div class="vhl-camara-acciones">
                        <span id="vhl-camara-estado">Buscando código QR…</span>
                        <button type="button" id="vhl-btn-camara-cancelar" class="vhl-copiar">
                            <i class="fas fa-xmark"></i> Cancelar
                        </button>
                    </div>
                </div>

                <div class="vhl-buscar-wrap">
                    <button type="submit" id="vhl-boton-validar" class="vhl-btn-buscar" disabled>
                        <i class="fas fa-shield-halved"></i> VALIDAR
                    </button>
                </div>
            </form>

            <div id="vhl-validar-resultado">
                <div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-qrcode"></i></div>
                    <p>No se ha cargado un QR.</p>
                </div>
            </div>

            <canvas id="vhl-qr-canvas" style="display: none;"></canvas>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('vendor/jsqr/jsQR.js') }}"></script>
    <script>
        (function () {
            const tabGenerar = document.getElementById('vhl-tab-generar');
            const tabVer = document.getElementById('vhl-tab-ver');
            const panelGenerar = document.getElementById('vhl-panel-generar');
            const panelVer = document.getElementById('vhl-panel-ver');

            const formBuscar = document.getElementById('vhl-form-buscar');
            const bundleIdInput = document.getElementById('vhl-bundle-id');
            const servidorSelect = document.getElementById('vhl-servidor');
            const botonBuscar = document.getElementById('vhl-boton-buscar');
            const resultado = document.getElementById('vhl-resultado');

            const qrFileInput = document.getElementById('vhl-qr-file');
            const fileLabel = document.getElementById('vhl-file-label');
            const fileNombre = document.getElementById('vhl-file-nombre');
            const formValidar = document.getElementById('vhl-form-validar');
            const botonValidar = document.getElementById('vhl-boton-validar');
            const validarPassCode = document.getElementById('vhl-validar-pass-code');
            const validarResultado = document.getElementById('vhl-validar-resultado');
            const canvas = document.getElementById('vhl-qr-canvas');
            let qrDecodificado = null;

            const btnCamara = document.getElementById('vhl-btn-camara');
            const camaraPanel = document.getElementById('vhl-camara-panel');
            const camaraVideo = document.getElementById('vhl-camara-video');
            const camaraEstado = document.getElementById('vhl-camara-estado');
            const btnCamaraCancelar = document.getElementById('vhl-btn-camara-cancelar');
            let camaraStream = null;
            let camaraFrameId = null;

            const urlBuscar = @json(route('vhl.buscar'));
            const urlGenerar = @json(route('vhl.generar'));
            const urlValidar = @json(route('vhl.validar'));
            const token = @json(csrf_token());

            function activarTab(modo) {
                const esGenerar = modo === 'generar';
                tabGenerar.classList.toggle('is-inactive', ! esGenerar);
                tabVer.classList.toggle('is-inactive', esGenerar);
                panelGenerar.style.display = esGenerar ? '' : 'none';
                panelVer.style.display = esGenerar ? 'none' : '';

                if (esGenerar) {
                    detenerCamara();
                }
            }

            tabGenerar.addEventListener('click', () => activarTab('generar'));
            tabVer.addEventListener('click', () => activarTab('ver'));

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

            function fechaPorDefecto() {
                const fecha = new Date(Date.now() + 90 * 24 * 60 * 60 * 1000);
                const pad = (n) => String(n).padStart(2, '0');
                return `${fecha.getFullYear()}-${pad(fecha.getMonth() + 1)}-${pad(fecha.getDate())}T${pad(fecha.getHours())}:${pad(fecha.getMinutes())}`;
            }

            formBuscar.addEventListener('submit', async function (e) {
                e.preventDefault();

                const bundleId = bundleIdInput.value.trim();
                if (! bundleId) {
                    return;
                }

                botonBuscar.disabled = true;
                resultado.innerHTML = estadoVacio('Buscando…');

                try {
                    const response = await fetch(urlBuscar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: new URLSearchParams({
                            bundle_id: bundleId,
                            servidor_fhir: servidorSelect.value,
                        }),
                    });

                    const data = await response.json();

                    if (response.ok) {
                        resultado.innerHTML = data.html;
                        const expiresInput = document.getElementById('vhl-expires-on');
                        if (expiresInput) {
                            expiresInput.value = fechaPorDefecto();
                        }
                    } else {
                        resultado.innerHTML = estadoError(data.error ?? 'No se pudo realizar la búsqueda.');
                    }
                } catch (e) {
                    resultado.innerHTML = estadoError('No se pudo conectar con el servidor.');
                } finally {
                    botonBuscar.disabled = false;
                }
            });

            resultado.addEventListener('submit', async function (e) {
                const form = e.target.closest('#vhl-form-generar');
                if (! form) {
                    return;
                }

                e.preventDefault();

                const boton = document.getElementById('vhl-boton-generar');
                const qrArea = document.getElementById('vhl-qr-resultado');
                boton.disabled = true;
                boton.textContent = 'Generando…';
                qrArea.innerHTML = '';

                try {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();
                    for (const [key, value] of formData.entries()) {
                        params.append(key, value);
                    }

                    const response = await fetch(urlGenerar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: params,
                    });

                    const data = await response.json();

                    if (response.ok) {
                        qrArea.innerHTML = `<div class="vhl-qr-card">
                            <img src="${data.qr}" alt="Código QR del VHL" width="260" height="260">
                            <div class="vhl-codigo">${data.codigo}</div>
                            <div class="vhl-acciones-qr">
                                <a href="${data.qr}" download="vhl-${form.bundle_id.value}.png" class="vhl-copiar">
                                    <i class="fas fa-download"></i> Descargar QR
                                </a>
                                <button type="button" class="vhl-copiar" id="vhl-btn-copiar">
                                    <i class="fas fa-copy"></i> Copiar código
                                </button>
                            </div>
                        </div>`;

                        document.getElementById('vhl-btn-copiar').addEventListener('click', function () {
                            navigator.clipboard.writeText(data.codigo);
                            this.innerHTML = '<i class="fas fa-check"></i> Copiado';
                        });
                    } else {
                        qrArea.innerHTML = estadoError(data.error ?? 'No se pudo generar el VHL.');
                    }
                } catch (e) {
                    qrArea.innerHTML = estadoError('No se pudo conectar con el servidor.');
                } finally {
                    boton.disabled = false;
                    boton.innerHTML = '<i class="fas fa-qrcode"></i> GENERAR VHL';
                }
            });

            // --- Ver VHL: leer el QR de una imagen (en el navegador) y validarlo ---

            function procesarArchivo(file) {
                qrDecodificado = null;
                botonValidar.disabled = true;
                fileLabel.classList.remove('is-listo', 'is-error');

                if (! file) {
                    fileNombre.textContent = 'Subir o arrastrar imagen del QR';
                    return;
                }

                if (! file.type.startsWith('image/')) {
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
                        botonValidar.disabled = false;
                    } else {
                        fileNombre.textContent = 'No se pudo leer un código VHL en esa imagen.';
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

            // --- Leer el QR con la cámara de la PC ---

            async function iniciarCamara() {
                if (! navigator.mediaDevices || ! navigator.mediaDevices.getUserMedia) {
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
                botonValidar.disabled = true;
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
                if (! camaraStream) {
                    return;
                }

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
                        botonValidar.disabled = false;
                        return;
                    }
                }

                camaraFrameId = requestAnimationFrame(escanearFrame);
            }

            btnCamara.addEventListener('click', iniciarCamara);
            btnCamaraCancelar.addEventListener('click', detenerCamara);

            function descargarJson(contenido, nombreArchivo) {
                const blob = new Blob([contenido], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = nombreArchivo;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }

            function renderValidacion(data) {
                const pasos = Object.values(data.validationStatus || {}).map(function (paso) {
                    const ok = paso.status === 'SUCCESS';
                    return `<li class="vhl-paso ${ok ? 'vhl-paso--ok' : 'vhl-paso--error'}">
                        <i class="fas ${ok ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                        <span>${paso.description}${paso.error ? ': ' + paso.error : ''}</span>
                    </li>`;
                }).join('');

                let manifest = '';
                if (data.shLinkContent) {
                    const vence = data.shLinkContent.exp ? new Date(data.shLinkContent.exp).toLocaleString() : '—';
                    manifest = `<div class="vhl-manifest">
                        <p><strong>Emisor:</strong> ${data.shLinkContent.label ?? '—'}</p>
                        <p><strong>Válido hasta:</strong> ${vence}</p>
                    </div>`;
                }

                const descargarBtn = data.bundleJson
                    ? `<button type="button" class="vhl-copiar vhl-descargar-json" id="vhl-btn-descargar-json">
                        <i class="fas fa-download"></i> Descargar JSON
                    </button>`
                    : '';

                const panelFicha = data.fichaHtml
                    ? `<div class="visor-panel visor-panel--light">${data.fichaHtml}</div>`
                    : `<div class="visor-panel visor-panel--light">${estadoVacio(data.errorFicha || 'No se pudo obtener la ficha.')}</div>`;

                setTimeout(function () {
                    const boton = document.getElementById('vhl-btn-descargar-json');
                    if (boton) {
                        boton.addEventListener('click', function () {
                            descargarJson(data.bundleJson, 'vhl-bundle.json');
                        });
                    }
                }, 0);

                return `<div class="vhl-split">
                    <div class="visor-panel visor-panel--light">
                        <div class="vhl-pasos-panel">
                            <p class="vhl-subtitulo">Pasos de validación</p>
                            <ul class="vhl-pasos">${pasos}</ul>
                            ${manifest}
                            ${descargarBtn}
                        </div>
                    </div>
                    ${panelFicha}
                </div>`;
            }

            formValidar.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (! qrDecodificado) {
                    return;
                }

                const passCode = validarPassCode.value.trim();
                botonValidar.disabled = true;
                validarResultado.innerHTML = estadoVacio('Validando…');

                try {
                    const response = await fetch(urlValidar, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: new URLSearchParams({
                            qr_code_content: qrDecodificado,
                            pass_code: passCode,
                        }),
                    });

                    const data = await response.json();

                    validarResultado.innerHTML = response.ok
                        ? renderValidacion(data)
                        : estadoError(data.error ?? 'No se pudo validar el VHL.');
                } catch (e) {
                    validarResultado.innerHTML = estadoError('No se pudo conectar con el servidor.');
                } finally {
                    botonValidar.disabled = false;
                }
            });
        })();
    </script>
@stop
