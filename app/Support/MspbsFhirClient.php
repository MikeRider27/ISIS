<?php

namespace App\Support;

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
