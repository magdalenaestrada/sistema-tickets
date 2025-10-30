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

        $client = new Client([
            'base_uri' => 'https://api.decolecta.com/v1/',
            'verify' => false,
        ]);

        // Si tiene 11 dígitos es RUC → usar endpoint avanzado
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
                'http_errors' => true, // Lanza error si status >= 400
            ]);

            $responseData = json_decode($response->getBody(), true);
            return response()->json($responseData, 200);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody()->getContents();

            return response()->json([
                'error' => "Error al consultar la API: {$statusCode}",
                'detalle' => json_decode($body, true)
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Excepción interna',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
