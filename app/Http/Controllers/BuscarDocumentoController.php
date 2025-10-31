<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BuscarDocumentoController extends Controller
{
    public function buscarDocumento(Request $request)
    {
        $documento = $request->input('documento');
        $token = env('APIS_TOKEN');

        if (!$documento) {
            return response()->json(['error' => 'Debe ingresar un número de documento.'], 400);
        }

        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.decolecta.com/v1/',
            'verify' => false,
        ]);

        $apiEndpoint = strlen($documento) === 11
            ? "sunat/ruc/full?numero={$documento}"
            : "reniec/dni?numero={$documento}";

        try {
            $response = $client->request('GET', $apiEndpoint, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // 🔹 Normalizamos la respuesta según el tipo de documento
            if (strlen($documento) === 8) {
                // DNI
                $resultado = [
                    'nombres' => $data['first_name'] ?? '',
                    'apellido_paterno' => $data['first_last_name'] ?? '',
                    'apellido_materno' => $data['second_last_name'] ?? '',
                    'documento' => $data['document_number'] ?? '',
                ];
            } else {
                // RUC
                $resultado = [
                    'razonSocial' => $data['razon_social'] ?? $data['nombre'] ?? '',
                    'documento' => $data['ruc'] ?? $data['numeroDocumento'] ?? '',
                ];
            }

            return response()->json($resultado);
        } catch (ClientException $e) {
            return response()->json([
                'error' => 'Documento no encontrado',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
