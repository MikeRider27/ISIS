<?php

namespace App\Support;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class MspbsFhirClient
{
    public function buscarDocumentos(string $identificador): array
    {
        $bundle = $this->get('DocumentReference/', [
            'patient.identifier' => $identificador,
            '_format' => 'json',
            'status' => 'current',
        ])->json();

        if (($bundle['resourceType'] ?? null) !== 'Bundle') {
            throw new RuntimeException('La respuesta del servidor no tiene el formato esperado.');
        }

        return collect($bundle['entry'] ?? [])
            ->map(fn ($entry) => $this->resumenDocumento($entry['resource'] ?? []))
            ->filter()
            ->values()
            ->all();
    }

    public function obtenerBundle(string $ref): array
    {
        if (! preg_match('#^[A-Za-z]+/[A-Za-z0-9\-\.]+$#', $ref)) {
            throw new RuntimeException('Referencia de documento inválida.');
        }

        return $this->validarBundle($this->get($ref, ['_format' => 'json'])->json());
    }

    public function obtenerBundlePorId(string $baseUrl, string $id): array
    {
        if (! preg_match('#^[A-Za-z0-9\-\.]+$#', $id)) {
            throw new RuntimeException('Identificador de Bundle inválido.');
        }

        return $this->validarBundle($this->getDesde($baseUrl, "Bundle/{$id}", ['_format' => 'json'])->json());
    }

    public function obtenerPaciente(string $referencia): ?array
    {
        if (! preg_match('#^Patient/([A-Za-z0-9\-\.]+)$#', $referencia, $m)) {
            return null;
        }

        try {
            return $this->get('Patient/'.$m[1], ['_format' => 'json'])->json();
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * Emite un VHL (Virtual Health Link) a partir de un Bundle ya filtrado,
     * devolviendo el código HC1: listo para codificar en un QR.
     */
    public function emitirVhl(array $bundle, string $passCode, string $expiresOn): string
    {
        try {
            $response = Http::timeout(20)->post(config('services.mspbs.vshc_issuance_url'), [
                'expiresOn' => $expiresOn,
                'jsonContent' => json_encode($bundle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'passCode' => $passCode,
            ]);
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el servicio de emisión de VHL.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El servicio de emisión de VHL respondió con un error ('.$response->status().').');
        }

        $codigo = trim($response->body(), "\" \t\n\r");

        if (! str_starts_with($codigo, 'HC1:')) {
            throw new RuntimeException('La respuesta del servicio de emisión no tiene el formato esperado.');
        }

        return $codigo;
    }

    /**
     * Valida un código VHL (ya decodificado desde el QR) contra el servicio
     * de validación del MSPBS, devolviendo el detalle de la validación.
     */
    public function validarVhl(string $qrCodeContent, string $passCode): array
    {
        try {
            $response = Http::timeout(20)->post(config('services.mspbs.vshc_validation_url'), [
                'qrCodeContent' => $qrCodeContent,
                'passCode' => $passCode,
            ]);
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el servicio de validación de VHL.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El servicio de validación de VHL respondió con un error ('.$response->status().').');
        }

        $data = $response->json();

        if (! is_array($data) || ! isset($data['validationStatus'])) {
            throw new RuntimeException('La respuesta del servicio de validación no tiene el formato esperado.');
        }

        return $data;
    }

    /**
     * Resuelve el manifiesto de un VHL ya validado (shLinkContent.url) y
     * descarga el Bundle IPS que referencia, listo para resumir con
     * FhirIpsSummarizer.
     */
    public function resolverManifiesto(string $manifestUrl, string $passCode): array
    {
        $this->validarHostGdhcn($manifestUrl);

        try {
            $response = Http::timeout(20)->post($manifestUrl, [
                'recipient' => config('app.name', 'ISIS'),
                'passcode' => $passCode,
            ]);
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el manifiesto del VHL.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El manifiesto del VHL respondió con un error ('.$response->status().').');
        }

        $location = $response->json('files.0.location');

        if (! $location) {
            throw new RuntimeException('El manifiesto del VHL no incluyó una ubicación de documento.');
        }

        $this->validarHostGdhcn($location);

        try {
            $bundleResponse = Http::timeout(20)->get($location);
        } catch (Throwable) {
            throw new RuntimeException('No se pudo obtener el documento del VHL.');
        }

        if (! $bundleResponse->successful()) {
            throw new RuntimeException('El documento del VHL respondió con un error ('.$bundleResponse->status().').');
        }

        return $this->validarBundle($bundleResponse->json());
    }

    /**
     * Invoca la operación $icvp del mediador MSPBS sobre un Bundle ya
     * publicado en el servidor FHIR, devolviendo un certificado (QR en data
     * URI + código HC1) por cada vacuna del Bundle.
     *
     * El mediador solo emite un certificado por llamada: si el Bundle
     * tiene una única Immunization alcanza con invocar $icvp sin
     * parámetros, pero si tiene varias hay que invocarlo una vez por
     * cada una indicando `immunizationId`.
     */
    public function emitirIcvp(string $bundleId): array
    {
        if (! preg_match('#^[A-Za-z0-9\-\.]+$#', $bundleId)) {
            throw new RuntimeException('Identificador de Bundle inválido.');
        }

        $bundle = $this->obtenerBundle("Bundle/{$bundleId}");
        $nombrePaciente = $this->nombrePacienteDesdeBundle($bundle);
        $inmunizaciones = $this->recursosPorTipo($bundle, ['Immunization']);

        if ($inmunizaciones->isEmpty()) {
            throw new RuntimeException('El Bundle no contiene ninguna vacuna (Immunization).');
        }

        $certificados = $inmunizaciones
            ->map(function (array $inmunizacion) use ($bundleId, $inmunizaciones) {
                $certificado = $this->solicitarCertificadoIcvp(
                    $bundleId,
                    $inmunizaciones->count() > 1 ? $inmunizacion['id'] : null,
                );

                return $certificado
                    ? [...$certificado, ...$this->infoVacuna($inmunizacion['resource'])]
                    : null;
            })
            ->filter()
            ->values()
            ->all();

        if (! $certificados) {
            throw new RuntimeException('La respuesta del mediador no incluyó ningún documento ICVP.');
        }

        return [
            'certificados' => $certificados,
            'nombrePaciente' => $nombrePaciente,
        ];
    }

    private function solicitarCertificadoIcvp(string $bundleId, ?string $immunizationId): ?array
    {
        $url = config('services.mspbs.mediator_url')."/fhir/Bundle/{$bundleId}/\$icvp";

        try {
            $response = Http::timeout(30)->acceptJson()->get($url, array_filter([
                'immunizationId' => $immunizationId,
            ]));
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el mediador de ICVP.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El mediador de ICVP respondió con un error ('.$response->status().').');
        }

        $documento = collect($response->json('entry', []))
            ->map(fn ($entry) => $entry['resource'] ?? [])
            ->first(fn ($resource) => ($resource['resourceType'] ?? null) === 'DocumentReference');

        return $documento ? $this->certificadoDesdeDocumentReference($documento) : null;
    }

    /**
     * Invoca la operación $meow del mediador MSPBS sobre un Bundle ya
     * publicado en el servidor FHIR (perfil MedicationOverview),
     * devolviendo un certificado (QR + código HC1) por cada medicamento
     * del Bundle.
     *
     * A diferencia de $icvp, la respuesta del mediador solo incluye el
     * código HC1 (sin imagen), por lo que el QR se genera localmente.
     *
     * NOTA: el parámetro para seleccionar el medicamento cuando hay más
     * de uno (`medicationId`) se definió por analogía con `immunizationId`
     * de $icvp, ya que no había en el servidor MSPBS ningún Bundle MEOW
     * con más de un medicamento para confirmarlo contra el mediador real.
     */
    public function emitirMeow(string $bundleId): array
    {
        if (! preg_match('#^[A-Za-z0-9\-\.]+$#', $bundleId)) {
            throw new RuntimeException('Identificador de Bundle inválido.');
        }

        $bundle = $this->obtenerBundle("Bundle/{$bundleId}");
        $nombrePaciente = $this->nombrePacienteDesdeBundle($bundle);
        $medicaciones = $this->recursosPorTipo($bundle, ['MedicationStatement', 'MedicationRequest']);

        if ($medicaciones->isEmpty()) {
            throw new RuntimeException('El Bundle no contiene ningún medicamento.');
        }

        $certificados = $medicaciones
            ->map(function (array $medicacion) use ($bundleId, $medicaciones) {
                $certificado = $this->solicitarCertificadoMeow(
                    $bundleId,
                    $medicaciones->count() > 1 ? $medicacion['id'] : null,
                );

                return $certificado
                    ? [...$certificado, ...$this->infoMedicamento($medicacion['resource'])]
                    : null;
            })
            ->filter()
            ->values()
            ->all();

        if (! $certificados) {
            throw new RuntimeException('La respuesta del mediador no incluyó ningún documento MEOW.');
        }

        return [
            'certificados' => $certificados,
            'nombrePaciente' => $nombrePaciente,
        ];
    }

    private function solicitarCertificadoMeow(string $bundleId, ?string $medicationId): ?array
    {
        $url = config('services.mspbs.mediator_url')."/fhir/Bundle/{$bundleId}/\$meow";

        try {
            $response = Http::timeout(30)->acceptJson()->get($url, array_filter([
                'medicationId' => $medicationId,
            ]));
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el mediador de MEOW.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El mediador de MEOW respondió con un error ('.$response->status().').');
        }

        $documento = collect($response->json('entry', []))
            ->map(fn ($entry) => $entry['resource'] ?? [])
            ->first(fn ($resource) => ($resource['resourceType'] ?? null) === 'DocumentReference');

        return $documento ? $this->certificadoMeowDesdeDocumentReference($documento) : null;
    }

    /**
     * Código y nombre del medicamento a partir de un recurso
     * MedicationStatement/MedicationRequest, para mostrar en el listado.
     */
    private function infoMedicamento(array $resource): array
    {
        $concepto = $resource['medicationCodeableConcept'] ?? null;

        if ($concepto) {
            $coding = $concepto['coding'][0] ?? [];

            return [
                'medicamentoCodigo' => $coding['code'] ?? null,
                'medicamentoNombre' => $concepto['text'] ?? $coding['display'] ?? 'Medicamento',
            ];
        }

        return [
            'medicamentoCodigo' => null,
            'medicamentoNombre' => $resource['medicationReference']['display'] ?? 'Medicamento',
        ];
    }

    private function certificadoMeowDesdeDocumentReference(array $documento): ?array
    {
        $texto = collect($documento['content'] ?? [])
            ->first(fn ($c) => str_starts_with($c['attachment']['data'] ?? '', 'HC1:'));

        if (! $texto) {
            return null;
        }

        $codigo = $texto['attachment']['data'];

        $qr = (new QRCode(new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 6,
        ])))->render($codigo);

        return [
            'qr' => $qr,
            'codigo' => $codigo,
        ];
    }

    /**
     * Código y nombre de la vacuna a partir de un recurso Immunization,
     * para mostrar en el listado. Se usa la codificación SNOMED CT del
     * vaccineCode (no la primera codificación, que puede ser un id de
     * producto de otro sistema, como PreQualProductIDs).
     */
    private function infoVacuna(array $resource): array
    {
        $codings = collect($resource['vaccineCode']['coding'] ?? []);

        $coding = $codings->first(fn ($c) => str_contains($c['system'] ?? '', 'snomed.info/sct'))
            ?? $codings->first()
            ?? [];

        return [
            'vacunaCodigo' => $coding['code'] ?? null,
            'vacunaNombre' => $coding['display'] ?? $resource['vaccineCode']['text'] ?? 'Vacuna',
        ];
    }

    /**
     * Recursos del Bundle que coincidan con alguno de los tipos indicados,
     * junto con su identificador (el id del recurso o, si no tiene, la
     * parte final de su fullUrl — formato que esperan los parámetros
     * `immunizationId`/`medicationId` del mediador).
     */
    private function recursosPorTipo(array $bundle, array $tiposRecurso): \Illuminate\Support\Collection
    {
        return collect($bundle['entry'] ?? [])
            ->filter(fn ($entry) => in_array($entry['resource']['resourceType'] ?? null, $tiposRecurso, true))
            ->map(function ($entry) {
                $id = $entry['resource']['id'] ?? null;

                if (! $id) {
                    $fullUrl = $entry['fullUrl'] ?? '';

                    $id = match (true) {
                        str_starts_with($fullUrl, 'urn:uuid:') => substr($fullUrl, strlen('urn:uuid:')),
                        str_contains($fullUrl, '/') => substr($fullUrl, strrpos($fullUrl, '/') + 1),
                        default => $fullUrl,
                    };
                }

                return ['id' => $id, 'resource' => $entry['resource']];
            })
            ->filter(fn ($item) => $item['id'] !== '')
            ->values();
    }

    private function certificadoDesdeDocumentReference(array $documento): ?array
    {
        $contenidos = collect($documento['content'] ?? []);

        $imagen = $contenidos->first(
            fn ($c) => str_starts_with($c['attachment']['contentType'] ?? '', 'image/')
        );

        $texto = $contenidos->first(
            fn ($c) => str_starts_with($c['attachment']['data'] ?? '', 'HC1:')
        );

        if (! $imagen || ! $texto) {
            return null;
        }

        return [
            'qr' => 'data:'.$imagen['attachment']['contentType'].';base64,'.$imagen['attachment']['data'],
            'codigo' => $texto['attachment']['data'],
        ];
    }

    private function nombrePacienteDesdeBundle(array $bundle): ?string
    {
        $paciente = collect($bundle['entry'] ?? [])
            ->map(fn ($entry) => $entry['resource'] ?? [])
            ->first(fn ($resource) => ($resource['resourceType'] ?? null) === 'Patient');

        if (! $paciente) {
            return null;
        }

        $name = $paciente['name'][0] ?? [];

        return $name['text'] ?? trim(($name['given'][0] ?? '').' '.($name['family'] ?? '')) ?: null;
    }

    private function validarHostGdhcn(string $url): void
    {
        $esperado = parse_url(config('services.mspbs.vshc_validation_url'), PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host || $host !== $esperado) {
            throw new RuntimeException('La URL del manifiesto no proviene de un servidor confiable.');
        }
    }

    private function validarBundle(array $data): array
    {
        if (($data['resourceType'] ?? null) !== 'Bundle') {
            throw new RuntimeException('El documento obtenido no es un Bundle de FHIR.');
        }

        return $data;
    }

    private function get(string $path, array $query): Response
    {
        return $this->getDesde($this->baseUrl(), $path, $query);
    }

    private function getDesde(string $baseUrl, string $path, array $query): Response
    {
        try {
            $response = Http::timeout(15)->acceptJson()->get(rtrim($baseUrl, '/').'/'.$path, $query);
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el servidor FHIR indicado.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('El servidor FHIR respondió con un error ('.$response->status().').');
        }

        return $response;
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.mspbs.fhir_url'), '/');
    }

    private function resumenDocumento(array $resource): ?array
    {
        if (($resource['resourceType'] ?? null) !== 'DocumentReference') {
            return null;
        }

        $attachment = $resource['content'][0]['attachment'] ?? [];
        $ref = $attachment['url'] ?? null;

        if (! $ref) {
            return null;
        }

        $titulo = $resource['type']['text']
            ?? $resource['type']['coding'][0]['display']
            ?? $attachment['title']
            ?? 'Documento';

        return [
            'ref' => $ref,
            'subject' => $resource['subject']['reference'] ?? null,
            'fecha' => $resource['date'] ?? null,
            'titulo' => $titulo,
        ];
    }
}
