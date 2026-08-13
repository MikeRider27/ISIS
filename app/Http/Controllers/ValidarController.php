<?php

namespace App\Http\Controllers;

use App\Support\HcertValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ValidarController extends Controller
{
    public function __construct(private readonly HcertValidator $validador) {}

    public function index(): View
    {
        return view('validar.index');
    }

    public function verificar(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code_content' => ['required', 'string', 'starts_with:HC1:'],
        ]);

        try {
            $resultado = $this->validador->validar($request->input('qr_code_content'));
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        return response()->json($resultado);
    }
}
