<?php

namespace App\Http\Controllers;

use App\Support\MspbsFhirClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MeowController extends Controller
{
    public function __construct(private readonly MspbsFhirClient $fhir) {}

    public function index(): View
    {
        return view('meow.index');
    }

    public function generar(Request $request): JsonResponse
    {
        $request->validate([
            'bundle_id' => ['required', 'string', 'max:100'],
        ]);

        try {
            $resultado = $this->fhir->emitirMeow($request->input('bundle_id'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json($resultado);
    }
}
