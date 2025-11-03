<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BuscarDocumentoController extends Controller
{
    public function buscarDocumento(Request $request)
    {
        $numero = $request->get('documento');

        if (!$numero) {
            return response()->json(['error' => 'Debe ingresar un número de documento.'], 400);
        }

        $token = env('APIS_TOKEN');
        $baseUrl = 'https://api.decolecta.com/v1/';

        try {
            // Determinar tipo de documento (DNI o RUC)
            if (strlen($numero) === 8) {
                $endpoint = "reniec/dni?numero={$numero}";
            } elseif (strlen($numero) === 11) {
                $endpoint = "sunat/ruc?numero={$numero}";
            } else {
                return response()->json(['error' => 'Número de documento inválido.'], 400);
            }

            // Llamada a la API
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ])->get($baseUrl . $endpoint);

            if ($response->failed()) {
                return response()->json(['error' => 'Documento no encontrado'], 404);
            }

            $data = $response->json();

            // ✅ Normalizar respuesta
            if (strlen($numero) === 8) {
                // DNI - RENIEC
                $resultado = [
                    'tipo' => 'DNI',
                    'documento' => $data['document_number'] ?? $numero,
                    'nombres' => $data['first_name'] ?? '',
                    'apellido_paterno' => $data['first_last_name'] ?? '',
                    'apellido_materno' => $data['second_last_name'] ?? '',
                    'nombre_completo' => $data['full_name'] ?? '',
                ];
            } else {
                // RUC - SUNAT
                $resultado = [
                    'tipo' => 'RUC',
                    'documento' => $data['numero_documento'] ?? $numero,
                    'razon_social' => $data['razon_social'] ?? '',
                    'direccion' => $data['direccion'] ?? '',
                    'estado' => $data['estado'] ?? '',
                    'condicion' => $data['condicion'] ?? '',
                    'departamento' => $data['departamento'] ?? '',
                    'provincia' => $data['provincia'] ?? '',
                    'distrito' => $data['distrito'] ?? '',
                ];
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno en la consulta',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
