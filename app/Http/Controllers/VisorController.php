<?php

namespace App\Http\Controllers;

use App\Support\FhirIpsSummarizer;
use App\Support\MspbsFhirClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class VisorController extends Controller
{
    public function __construct(private readonly MspbsFhirClient $fhir) {}

    public function index(?string $id = null): View
    {
        if (! $id) {
            return view('visor.index');
        }

        try {
            $data = $this->fhir->obtenerBundle("Bundle/{$id}");
        } catch (RuntimeException $e) {
            return view('visor.index', ['error' => $e->getMessage()]);
        }

        return view('visor.index', [
            'ficha' => FhirIpsSummarizer::summarize($data),
            'raw' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function store(Request $request): View|RedirectResponse|JsonResponse
    {
        $request->validate([
            'json' => ['required', 'string'],
        ]);

        $data = json_decode($request->input('json'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fallo($request, 'El texto pegado no es un JSON válido: '.json_last_error_msg());
        }

        if (($data['resourceType'] ?? null) !== 'Bundle') {
            return $this->fallo($request, 'El JSON debe ser un Bundle de FHIR (resourceType: "Bundle").');
        }

        $ficha = FhirIpsSummarizer::summarize($data);

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('visor._ficha', ['ficha' => $ficha])->render(),
            ]);
        }

        return view('visor.index', [
            'ficha' => $ficha,
            'raw' => $request->input('json'),
        ]);
    }

    private function fallo(Request $request, string $mensaje): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['error' => $mensaje], 422);
        }

        return back()->withInput()->withErrors(['json' => $mensaje]);
    }
}
