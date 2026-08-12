<?php

namespace App\Http\Controllers;

use App\Support\FhirIpsSummarizer;
use App\Support\MspbsFhirClient;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class VhlController extends Controller
{
    private const RECURSOS_SELECCIONABLES = [
        'Condition',
        'MedicationStatement',
        'MedicationRequest',
        'AllergyIntolerance',
        'Procedure',
        'Immunization',
        'Observation',
        'Encounter',
    ];

    private const RECURSOS_ESTRUCTURALES = [
        'Composition',
        'Patient',
        'Practitioner',
        'Organization',
    ];

    public function __construct(private readonly MspbsFhirClient $fhir) {}

    public function index(): View
    {
        return view('vhl.index', [
            'servidores' => config('services.mspbs.fhir_servers'),
        ]);
    }

    public function buscar(Request $request): JsonResponse
    {
        $request->validate([
            'bundle_id' => ['required', 'string', 'max:100'],
            'servidor_fhir' => ['required', 'string'],
        ]);

        $servidor = $request->input('servidor_fhir');

        if (! array_key_exists($servidor, config('services.mspbs.fhir_servers'))) {
            return response()->json(['error' => 'Servidor FHIR no permitido.'], 422);
        }

        try {
            $bundle = $this->fhir->obtenerBundlePorId($servidor, $request->input('bundle_id'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $paciente = collect($bundle['entry'] ?? [])
            ->map(fn ($e) => $e['resource'] ?? [])
            ->first(fn ($r) => ($r['resourceType'] ?? null) === 'Patient');

        $nombrePaciente = null;
        if ($paciente) {
            $name = $paciente['name'][0] ?? [];
            $nombrePaciente = $name['text'] ?? trim(($name['given'][0] ?? '').' '.($name['family'] ?? '')) ?: null;
        }

        $seleccionables = collect($bundle['entry'] ?? [])
            ->map(fn ($entry) => $this->resumenSeleccionable($entry))
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'html' => view('vhl._seleccion', [
                'bundleId' => $request->input('bundle_id'),
                'servidor' => $servidor,
                'nombrePaciente' => $nombrePaciente,
                'seleccionables' => $seleccionables,
            ])->render(),
        ]);
    }

    public function generar(Request $request): JsonResponse
    {
        $request->validate([
            'bundle_id' => ['required', 'string', 'max:100'],
            'servidor_fhir' => ['required', 'string'],
            'seleccionados' => ['array'],
            'seleccionados.*' => ['string'],
            'pass_code' => ['required', 'string', 'regex:/^\d{4,8}$/'],
            'expires_on' => ['required', 'date'],
        ]);

        $servidor = $request->input('servidor_fhir');

        if (! array_key_exists($servidor, config('services.mspbs.fhir_servers'))) {
            return response()->json(['error' => 'Servidor FHIR no permitido.'], 422);
        }

        try {
            $bundle = $this->fhir->obtenerBundlePorId($servidor, $request->input('bundle_id'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $seleccionados = array_flip($request->input('seleccionados', []));

        $bundle['entry'] = collect($bundle['entry'] ?? [])
            ->filter(fn ($entry) => in_array($entry['resource']['resourceType'] ?? null, self::RECURSOS_ESTRUCTURALES, true)
                || isset($seleccionados[$entry['fullUrl'] ?? '']))
            ->values()
            ->all();

        try {
            $codigo = $this->fhir->emitirVhl(
                $bundle,
                $request->input('pass_code'),
                Carbon::parse($request->input('expires_on'))->utc()->toIso8601ZuluString(),
            );
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $qr = (new QRCode(new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 6,
        ])))->render($codigo);

        return response()->json([
            'qr' => $qr,
            'codigo' => $codigo,
        ]);
    }

    public function validar(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code_content' => ['required', 'string', 'starts_with:HC1:'],
            'pass_code' => ['required', 'string', 'regex:/^\d{4,8}$/'],
        ]);

        try {
            $resultado = $this->fhir->validarVhl(
                $request->input('qr_code_content'),
                $request->input('pass_code'),
            );
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $fichaHtml = null;
        $bundleJson = null;
        $errorFicha = null;
        $manifestUrl = $resultado['shLinkContent']['url'] ?? null;

        if ($manifestUrl) {
            try {
                $bundle = $this->fhir->resolverManifiesto($manifestUrl, $request->input('pass_code'));
                $fichaHtml = view('visor._ficha', ['ficha' => FhirIpsSummarizer::summarize($bundle)])->render();
                $bundleJson = json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } catch (RuntimeException $e) {
                $errorFicha = $e->getMessage();
            }
        }

        return response()->json([
            ...$resultado,
            'fichaHtml' => $fichaHtml,
            'bundleJson' => $bundleJson,
            'errorFicha' => $errorFicha,
        ]);
    }

    private function resumenSeleccionable(array $entry): ?array
    {
        $resource = $entry['resource'] ?? [];
        $tipo = $resource['resourceType'] ?? null;

        if (! in_array($tipo, self::RECURSOS_SELECCIONABLES, true)) {
            return null;
        }

        return [
            'fullUrl' => $entry['fullUrl'] ?? null,
            'tipo' => $tipo,
            'texto' => $this->textoRecurso($tipo, $resource),
        ];
    }

    private function textoRecurso(string $tipo, array $resource): string
    {
        $codigo = fn (?array $c) => $c['coding'][0]['display'] ?? $c['text'] ?? $c['coding'][0]['code'] ?? null;

        return match ($tipo) {
            'Condition', 'AllergyIntolerance', 'Procedure', 'Observation' => $codigo($resource['code'] ?? null) ?? $tipo,
            'MedicationStatement', 'MedicationRequest' => $resource['medicationReference']['display']
                ?? $codigo($resource['medicationCodeableConcept'] ?? null) ?? $tipo,
            'Immunization' => $codigo($resource['vaccineCode'] ?? null) ?? $tipo,
            'Encounter' => $codigo($resource['type'][0] ?? null) ?? ($resource['class']['code'] ?? $tipo),
            default => $tipo,
        };
    }
}
