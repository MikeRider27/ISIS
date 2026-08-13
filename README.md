# IPS_VHL

Aplicación Laravel para consultar, visualizar y compartir documentos de **Resumen Clínico Internacional (IPS)** integrados con el ecosistema FHIR del **MSPBS** (Ministerio de Salud Pública y Bienestar Social, Paraguay) y la red **GDHCN/RACSEL** (LACPass).

Permite pegar o buscar un Bundle IPS, visualizarlo como una ficha clínica legible, y generar o validar **VHL (Virtual Health Link)** — un código QR firmado que permite compartir un resumen clínico de forma segura y con PIN.

## Requisitos

- Docker y Docker Compose (el proyecto usa [Laravel Sail](https://laravel.com/docs/sail))
- Acceso de red saliente a los servidores del MSPBS:
  - `fhir.mspbs.gov.py` (servidor FHIR)
  - `gdncn.mspbs.gov.py` (emisión/validación de VHL)

## Instalación

```bash
cp .env.example .env
# completar DB_*, MSPBS_FHIR_URL, MSPBS_VHL_URL

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

La app queda disponible en `http://localhost:${APP_PORT}` (por defecto `80`, configurable en `.env`).

### Variables de entorno relevantes

| Variable | Descripción |
|---|---|
| `DB_*` | Conexión a PostgreSQL |
| `MSPBS_FHIR_URL` | Base del servidor FHIR del MSPBS (búsqueda de documentos y Bundles) |
| `MSPBS_VHL_URL` | Base del servicio de VHL del MSPBS (se completa con `/v2/vshcIssuance` y `/v2/vshcValidation`) |

Los servidores FHIR permitidos para las consultas están en `config/services.php` (`services.mspbs.fhir_servers`) como lista blanca — evita que el campo "Servidor FHIR" se use para SSRF hacia hosts arbitrarios.

## Autenticación

Login simple (sin autorregistro ni recuperación de contraseña) — los usuarios se crean manualmente, por ejemplo:

```bash
./vendor/bin/sail artisan tinker --execute="
\App\Models\User::create(['name' => 'Administrador', 'email' => 'admin@isis.local', 'password' => bcrypt('password')]);
"
```

## Módulos

### Home
Pantalla de bienvenida tras el login.

### Visor (`/visor`, `/visor/{id}`)
Pegá el JSON de un Bundle IPS y visualizalo en un panel dividido: a la izquierda un árbol JSON interactivo (colapsable, editable), a la derecha la ficha clínica resumida (paciente, médico, institución, problemas, medicación, alergias, procedimientos, encuentros, resultados, inmunizaciones).

`/visor/{id}` carga directamente el Bundle `Bundle/{id}` desde el servidor FHIR del MSPBS, sin necesidad de pegarlo a mano.

### Consultar IPS (`/consulta-ips`)
Busca los documentos disponibles de un paciente por identificador (transacción **ITI-67** — Find Document References) contra el servidor FHIR del MSPBS. Muestra una tabla paginada con título, fecha y un botón directo a **Visor** para cada documento encontrado.

### VHL (`/vhl`)
- **Generar VHL**: buscá un Bundle por ID + servidor FHIR, elegí qué recursos clínicos compartir, definí un PIN y una fecha de expiración, y generá un código QR (formato `HC1:`, compatible con certificados de salud digital) listo para descargar.
- **Ver VHL**: subí una imagen del QR (arrastrando, seleccionando el archivo, o escaneando con la cámara de la PC), decodificado en el navegador. Al ingresar el PIN, valida la firma del código contra el MSPBS, resuelve el manifiesto asociado y muestra la ficha clínica completa — con opción de descargar el Bundle original en JSON.

> La cámara requiere un [contexto seguro](https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts) (HTTPS, o `localhost`). Accediendo por IP en LAN sin HTTPS, los navegadores basados en Chromium permiten habilitarla manualmente vía `chrome://flags/#unsafely-treat-insecure-origin-as-secure`.

## Arquitectura

- `App\Support\FhirIpsSummarizer` — convierte un Bundle FHIR IPS en la estructura que renderiza la ficha clínica (`resources/views/visor/_ficha.blade.php`). Identifica secciones por código LOINC, no por el texto del título (que varía entre documentos).
- `App\Support\MspbsFhirClient` — encapsula todas las llamadas salientes al FHIR del MSPBS y a los endpoints GDHCN de emisión/validación/manifiesto de VHL.
- `resources/views/partials/ficha-styles.blade.php` — estilos compartidos de la ficha clínica, reutilizados por Visor, Consultar IPS y VHL.

## Stack

Laravel 13 · PostgreSQL · [AdminLTE](https://github.com/jeroennoten/Laravel-AdminLTE) · [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode) (generación de QR) · [jsQR](https://github.com/cozmo/jsQR) (lectura de QR en el navegador)
