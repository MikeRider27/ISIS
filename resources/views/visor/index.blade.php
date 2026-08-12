@extends('adminlte::page')

@section('title', 'Visor')

@section('content_header')
    <h1>Visor IPS</h1>
@stop

@section('css')
    <style>
        .visor-shell {
            display: flex;
            gap: 1rem;
            height: calc(100vh - 190px);
            min-height: 480px;
        }

        .visor-textarea {
            flex: 1;
            width: 100%;
            background: transparent;
            border: 0;
            color: #e6e8ee;
            font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .85rem;
            line-height: 1.5;
            padding: 1.25rem;
            resize: none;
        }

        .visor-textarea:focus {
            outline: none;
            box-shadow: none;
        }

        .visor-textarea::placeholder {
            color: #5b6274;
        }

        .visor-tree-edit {
            position: absolute;
            top: .75rem;
            right: .75rem;
            background: transparent;
            border: 0;
            color: #6b7280;
            cursor: pointer;
            padding: .3rem;
            line-height: 1;
            z-index: 2;
        }

        .visor-tree-edit:hover {
            color: #d7dae0;
        }

        .visor-tree {
            display: none;
            flex: 1;
            overflow: auto;
            padding: 1.25rem;
            font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .82rem;
            line-height: 1.6;
        }

        .jt-children {
            margin-left: 1.25rem;
        }

        .jt-line {
            white-space: pre;
        }

        .jt-toggle {
            cursor: pointer;
            user-select: none;
        }

        .jt-toggle:hover {
            background: rgba(255, 255, 255, .04);
        }

        .jt-arrow {
            display: inline-block;
            width: 1rem;
            color: #6b7280;
        }

        .jt-key {
            color: #e6e8ee;
        }

        .jt-punct {
            color: #6b7280;
        }

        .jt-string {
            color: #61afef;
        }

        .jt-number {
            color: #98c379;
        }

        .jt-bool,
        .jt-null {
            color: #c678dd;
        }

        .jt-summary {
            color: #6b7280;
            font-style: italic;
        }

        @media (max-width: 767px) {
            .visor-shell {
                flex-direction: column;
                height: auto;
            }

            .visor-panel {
                min-height: 320px;
            }

            .visor-cols {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @include('partials.ficha-styles')
@stop

@section('content')
    @isset($error)
        <div class="alert alert-danger">{{ $error }}</div>
    @endisset

    <div class="visor-shell">
        <div class="visor-panel visor-panel--dark">
            <button type="button" id="visor-tree-edit" class="visor-tree-edit" title="Editar JSON" hidden>
                <i class="fas fa-pencil-alt"></i>
            </button>

            <textarea id="visor-input" class="visor-textarea"
                placeholder="Pega el IPS aquí">{{ old('json', $raw ?? '') }}</textarea>

            <div id="visor-tree" class="visor-tree"></div>
        </div>

        <div class="visor-panel visor-panel--light" id="visor-resultado">
            @isset($ficha)
                @include('visor._ficha', ['ficha' => $ficha])
            @else
                <div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-clipboard-list"></i></div>
                    <p>No se ha cargado un IPS</p>
                </div>
            @endisset
        </div>
    </div>
@stop

@section('js')
    <script>
        (function () {
            const input = document.getElementById('visor-input');
            const arbol = document.getElementById('visor-tree');
            const botonEditar = document.getElementById('visor-tree-edit');
            const resultado = document.getElementById('visor-resultado');
            const url = @json(route('visor.store'));
            const token = @json(csrf_token());
            let timer = null;

            // --- Árbol JSON interactivo (panel izquierdo) ---

            function escaparHtml(texto) {
                return texto.replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[c]));
            }

            function claveHtml(clave) {
                return clave !== null
                    ? `<span class="jt-key">${escaparHtml(String(clave))}</span><span class="jt-punct">: </span>`
                    : '';
            }

            function valorHtml(valor) {
                if (typeof valor === 'string') {
                    return `<span class="jt-string">"${escaparHtml(valor)}"</span>`;
                }
                if (typeof valor === 'number') {
                    return `<span class="jt-number">${valor}</span>`;
                }
                if (typeof valor === 'boolean') {
                    return `<span class="jt-bool">${valor}</span>`;
                }
                return `<span class="jt-null">null</span>`;
            }

            function nodoPara(clave, valor, esUltimo) {
                const coma = esUltimo ? '' : ',';
                const esObjeto = valor !== null && typeof valor === 'object';

                if (! esObjeto) {
                    const linea = document.createElement('div');
                    linea.className = 'jt-line';
                    linea.innerHTML = claveHtml(clave) + valorHtml(valor) + `<span class="jt-punct">${coma}</span>`;
                    return linea;
                }

                const esArray = Array.isArray(valor);
                const entradas = esArray ? valor.map((v, i) => [i, v]) : Object.entries(valor);
                const abre = esArray ? '[' : '{';
                const cierra = esArray ? ']' : '}';

                if (entradas.length === 0) {
                    const linea = document.createElement('div');
                    linea.className = 'jt-line';
                    linea.innerHTML = claveHtml(clave) + `<span class="jt-punct">${abre}${cierra}${coma}</span>`;
                    return linea;
                }

                const nodo = document.createElement('div');
                nodo.className = 'jt-node';

                const cabecera = document.createElement('div');
                cabecera.className = 'jt-line jt-toggle';
                cabecera.innerHTML = '<span class="jt-arrow">▾</span>' + claveHtml(clave) +
                    `<span class="jt-punct">${abre}</span><span class="jt-summary" hidden> … ${entradas.length}</span>`;

                const hijos = document.createElement('div');
                hijos.className = 'jt-children';
                entradas.forEach(([k, v], idx) => {
                    hijos.appendChild(nodoPara(esArray ? String(k) : k, v, idx === entradas.length - 1));
                });

                const pie = document.createElement('div');
                pie.className = 'jt-line';
                pie.innerHTML = `<span class="jt-punct">${cierra}${coma}</span>`;

                const flecha = cabecera.querySelector('.jt-arrow');
                const resumen = cabecera.querySelector('.jt-summary');

                cabecera.addEventListener('click', function () {
                    const colapsando = hijos.style.display !== 'none';
                    hijos.style.display = colapsando ? 'none' : '';
                    pie.style.display = colapsando ? 'none' : '';
                    flecha.textContent = colapsando ? '▸' : '▾';
                    resumen.hidden = ! colapsando;
                });

                nodo.appendChild(cabecera);
                nodo.appendChild(hijos);
                nodo.appendChild(pie);

                return nodo;
            }

            function mostrarArbol(datos) {
                arbol.innerHTML = '';
                arbol.appendChild(nodoPara(null, datos, true));
                arbol.style.display = 'block';
                input.style.display = 'none';
                botonEditar.hidden = false;
            }

            function mostrarEditor() {
                arbol.style.display = 'none';
                input.style.display = '';
                botonEditar.hidden = true;
            }

            botonEditar.addEventListener('click', function () {
                mostrarEditor();
                input.focus();
            });

            // --- Ficha clínica (panel derecho, vía AJAX al servidor) ---

            function estadoVacio() {
                return `<div class="visor-empty">
                    <div class="visor-empty-icon"><i class="fas fa-clipboard-list"></i></div>
                    <p>No se ha cargado un IPS</p>
                </div>`;
            }

            function estadoError(mensaje) {
                return `<div class="visor-empty visor-error">
                    <div class="visor-empty-icon"><i class="fas fa-triangle-exclamation"></i></div>
                    <p>${mensaje}</p>
                </div>`;
            }

            async function procesar() {
                const texto = input.value.trim();

                if (! texto) {
                    resultado.innerHTML = estadoVacio();
                    return;
                }

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: 'json=' + encodeURIComponent(texto),
                    });

                    const data = await response.json();

                    resultado.innerHTML = response.ok
                        ? data.html
                        : estadoError(data.error ?? 'No se pudo procesar el IPS.');
                } catch (e) {
                    resultado.innerHTML = estadoError('No se pudo conectar con el servidor.');
                }
            }

            function alCambiar() {
                const texto = input.value.trim();

                if (texto) {
                    try {
                        mostrarArbol(JSON.parse(texto));
                    } catch (e) {
                        mostrarEditor();
                    }
                }

                procesar();
            }

            input.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(alCambiar, 600);
            });

            if (input.value.trim()) {
                alCambiar();
            }
        })();
    </script>
@stop
