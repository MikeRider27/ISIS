<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Cliente del servicio "hcert-validator" del WHO-ITB
 * (github.com/WorldHealthOrganization/WHO-ITB), que decodifica el CBOR/COSE
 * contenido en un código HC1 (ICVP/MEOW) y valida su firma contra la
 * trustlist GDHCN.
 */
class HcertValidator
{
    /**
     * Decodifica, verifica la firma e interpreta un código HC1 de ICVP o
     * MEOW, devolviendo una estructura lista para mostrar en pantalla.
     */
    public function validar(string $qrData): array
    {
        $decodificado = $this->decodificar($qrData);
        $verificacion = $this->verificarFirma($decodificado['cose']['_raw'] ?? []);

        return $this->interpretar($decodificado, $verificacion);
    }

    private function decodificar(string $qrData): array
    {
        $response = $this->post('/decode/hcert', [
            'qr_data' => $qrData,
            'include_raw' => true,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'No se pudo decodificar el código: '.($response->json('details') ?? $response->json('error') ?? 'formato inválido.')
            );
        }

        return $response->json();
    }

    private function verificarFirma(array $coseRaw): array
    {
        if (! $coseRaw) {
            throw new RuntimeException('El código decodificado no incluyó los datos COSE necesarios para verificar la firma.');
        }

        $response = $this->post('/verify/signature', [
            'cose_raw' => $coseRaw,
            'use_gdhcn' => true,
            'gdhcn_env' => config('services.who_itb.gdhcn_env'),
            'participant' => '-',
            'domain' => config('services.who_itb.domain'),
            'usage' => config('services.who_itb.usage'),
            'verify_did_proof' => false,
            'allow_remote_contexts' => true,
            'allow_unverified_trustlist' => true,
        ]);

        $data = $response->json();

        if (! is_array($data) || ! array_key_exists('valid', $data)) {
            throw new RuntimeException('El servicio de verificación de firma respondió con un formato inesperado.');
        }

        return $data;
    }

    private function post(string $path, array $body): \Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(20)->acceptJson()->post(
                config('services.who_itb.hcert_validator_url').$path,
                $body,
            );
        } catch (Throwable) {
            throw new RuntimeException('No se pudo conectar con el servicio validador (hcert-validator).');
        }
    }

    /**
     * Arma una estructura normalizada a partir de la respuesta de decode +
     * verify, detectando si el certificado es de ICVP (vacunas, clave -6)
     * o de MEOW (medicamentos, clave -7).
     */
    private function interpretar(array $decodificado, array $verificacion): array
    {
        $payload = $decodificado['payload'] ?? [];
        $hcert = $payload['-260'] ?? [];

        $tipo = 'desconocido';
        $datos = [];

        if (array_key_exists('-6', $hcert)) {
            $tipo = 'icvp';
            $datos = $hcert['-6'];
        } elseif (array_key_exists('-7', $hcert)) {
            $tipo = 'meow';
            $datos = $hcert['-7'];
        }

        $paciente = [
            'nombre' => $datos['n'] ?? null,
            'nacimiento' => $datos['dob'] ?? null,
            'sexo' => $datos['s'] ?? null,
            'identificador' => $datos['nid'] ?? $datos['id'] ?? null,
            'identificadorTipo' => $datos['ndt'] ?? $datos['dt'] ?? null,
        ];

        $items = match ($tipo) {
            'icvp' => collect([$datos['v'] ?? null])->filter()->map(fn ($v) => [
                'codigo' => $v['vp'] ?? null,
                'fecha' => $v['dt'] ?? null,
                'lote' => $v['bo'] ?? null,
                'validoDesde' => $v['vls'] ?? null,
            ])->values()->all(),
            'meow' => collect($datos['m'] ?? [])->map(fn ($m) => [
                'codigo' => $m['m'] ?? null,
                'nombre' => $m['r'] ?? null,
                'fecha' => $m['da'] ?? null,
                'dosis' => $m['d'] ?? null,
            ])->values()->all(),
            default => [],
        };

        return [
            'valido' => (bool) ($verificacion['valid'] ?? false),
            'mensajeFirma' => $verificacion['message'] ?? 'Sin información de verificación.',
            'tipo' => $tipo,
            'pais' => $payload['1'] ?? null,
            'paciente' => $paciente,
            'items' => $items,
            'payload' => $payload,
        ];
    }
}
