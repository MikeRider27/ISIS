<?php

namespace App\Http\Controllers;

use App\Support\MspbsFhirClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class ConsultaIpsController extends Controller
{
    public function __construct(private readonly MspbsFhirClient $fhir) {}

    public function index(Request $request): View
    {
        $resultado = null;
        $error = null;

        if ($request->filled('identificador')) {
            try {
                $resultado = $this->buscar($request->input('identificador'));
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }

        return view('consulta-ips.index', [
            'identificador' => $request->input('identificador'),
            'nombrePaciente' => $resultado['nombrePaciente'] ?? null,
            'documentos' => $resultado['documentos'] ?? null,
            'error' => $error,
        ]);
    }

    public function buscarAjax(Request $request): JsonResponse
    {
        $request->validate([
            'identificador' => ['required', 'string', 'max:100'],
        ]);

        try {
            $resultado = $this->buscar($request->input('identificador'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json([
            'html' => view('consulta-ips._resultados', $resultado)->render(),
        ]);
    }

    private function buscar(string $identificador): array
    {
        $documentos = $this->fhir->buscarDocumentos($identificador);

        $nombrePaciente = null;

        if ($primero = $documentos[0] ?? null) {
            if ($primero['subject'] && ($paciente = $this->fhir->obtenerPaciente($primero['subject']))) {
                $name = $paciente['name'][0] ?? [];
                $nombrePaciente = $name['text'] ?? trim(($name['given'][0] ?? '').' '.($name['family'] ?? '')) ?: null;
            }
        }

        $documentos = collect($documentos)
            ->map(fn ($doc) => [
                'id' => str_contains($doc['ref'], '/') ? substr($doc['ref'], strrpos($doc['ref'], '/') + 1) : $doc['ref'],
                'fecha' => $doc['fecha'],
                'fecha_formateada' => $this->formatearFecha($doc['fecha']),
                'titulo' => $doc['titulo'],
            ])
            ->filter(fn ($doc) => $doc['id'] !== '')
            ->values()
            ->all();

        return ['nombrePaciente' => $nombrePaciente, 'documentos' => $documentos];
    }

    private function formatearFecha(?string $fecha): ?string
    {
        if (! $fecha) {
            return null;
        }

        try {
            return Carbon::parse($fecha)->format('d/m/Y H:i');
        } catch (Throwable) {
            return $fecha;
        }
    }
}
