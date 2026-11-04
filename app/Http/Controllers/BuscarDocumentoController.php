<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BuscarDocumentoController extends Controller
{
    public function buscarDocumento(Request $request)
    {
        $numero = trim((string) $request->input('documento', ''));

        if (!preg_match('/^(\d{8}|\d{11})$/', $numero)) {
            return response()->json([
                'error' => 'Debe ingresar un DNI de 8 dígitos o un RUC de 11 dígitos.'
            ], 400);
        }

        try {
            $persona = Persona::where('documento', $numero)
                ->where('estado', 'A')
                ->first();

            if ($persona) {
                return response()->json($this->normalizarPersonaBD($persona));
            }

            $datosAPI = $this->consultarAPI($numero);

            if ($datosAPI) {
                $this->guardarPersona($datosAPI);

                return response()->json($datosAPI);
            }
            
            return response()->json(['error' => 'Documento no encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno en la consulta',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }

    private function normalizarPersonaBD(Persona $persona): array
    {
        $esDNI = strlen($persona->documento) === 8;

        if ($esDNI) {
            return [
                'tipo' => 'DNI',
                'documento' => $persona->documento,
                'nombres' => $persona->nombres,
                'apellido_paterno' => $this->extraerApellidoPaterno($persona->apellidos),
                'apellido_materno' => $this->extraerApellidoMaterno($persona->apellidos),
                'nombre_completo' => trim($persona->nombres . ' ' . $persona->apellidos),
                'origen' => 'base_datos', // Indicador de origen
            ];
        } else {
            return [
                'tipo' => 'RUC',
                'documento' => $persona->documento,
                'razon_social' => $persona->razon_social ?? $persona->nombres,
                'direccion' => $persona->direccion ?? '',
                'departamento' => $persona->distrito->provincia->departamento->nombre ?? '',
                'provincia' => $persona->distrito->provincia->nombre ?? '',
                'distrito' => $persona->distrito->nombre ?? '',
                'origen' => 'base_datos',
            ];
        }
    }



    private function consultarAPI(string $numero): ?array
    {
        $token = config('token.decolecta.token');
        $baseUrl = rtrim(config('token.decolecta.url'), '/');

        if (!$token || !$baseUrl) {
            \Log::error('Falta configurar el token o URL de APIsPERU.');
            return null;
        }

        $esDni = strlen($numero) === 8;
        $tipo = $esDni ? 'dni' : 'ruc';

        // Evitar duplicar /api/v1 si ya está incluido en la configuración.
        if (!str_ends_with($baseUrl, '/api/v1')) {
            $baseUrl .= '/api/v1';
        }

        $endpoint = "{$baseUrl}/{$tipo}/{$numero}";

        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->get($endpoint, [
                    'token' => $token,
                ]);

            if ($response->failed()) {
                \Log::warning('Error consultando APIsPERU', [
                    'tipo' => $tipo,
                    'numero' => $numero,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();

            if (!is_array($data) || ($data['success'] ?? true) === false) {
                return null;
            }

            if ($esDni) {
                return [
                    'tipo' => 'DNI',
                    'documento' => $data['dni'] ?? $numero,
                    'nombres' => $data['nombres'] ?? '',
                    'apellido_paterno' => $data['apellidoPaterno'] ?? '',
                    'apellido_materno' => $data['apellidoMaterno'] ?? '',
                    'nombre_completo' => trim(
                        ($data['nombres'] ?? '') . ' ' .
                            ($data['apellidoPaterno'] ?? '') . ' ' .
                            ($data['apellidoMaterno'] ?? '')
                    ),
                    'cod_verifica' => $data['codVerifica'] ?? null,
                    'origen' => 'api_externa',
                ];
            }

            return [
                'tipo' => 'RUC',
                'documento' => $data['ruc'] ?? $numero,
                'razon_social' => $data['razonSocial'] ?? '',
                'nombre_comercial' => $data['nombreComercial'] ?? '',
                'telefonos' => $data['telefonos'] ?? [],
                'direccion' => $data['direccion'] ?? '',
                'estado' => $data['estado'] ?? '',
                'condicion' => $data['condicion'] ?? '',
                'departamento' => $data['departamento'] ?? '',
                'provincia' => $data['provincia'] ?? '',
                'distrito' => $data['distrito'] ?? '',
                'ubigeo' => $data['ubigeo'] ?? '',
                'capital' => $data['capital'] ?? '',
                'origen' => 'api_externa',
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::warning('Error de conexión con APIsPERU', [
                'tipo' => $tipo,
                'numero' => $numero,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }




    private function extraerApellidoPaterno(?string $apellidos): string
    {
        if (!$apellidos) return '';
        $partes = explode(' ', trim($apellidos));
        return $partes[0] ?? '';
    }

    private function extraerApellidoMaterno(?string $apellidos): string
    {
        if (!$apellidos) return '';
        $partes = explode(' ', trim($apellidos));
        array_shift($partes); // Quitar el primero
        return implode(' ', $partes);
    }

    /**
     * OPCIONAL: Guardar persona en BD para futuras consultas
     */
    private function guardarPersona(array $datos): void
    {
        try {
            $esDNI = $datos['tipo'] === 'DNI';

            Persona::create([
                'tipo_documento_id' => $esDNI ? 1 : 2,
                'distrito_id' => 1,
                'documento' => $datos['documento'],
                'nombres' => $esDNI ? $datos['nombres'] : ($datos['razon_social'] ?? ''),
                'apellidos' => $esDNI ? trim(($datos['apellido_paterno'] ?? '') . ' ' . ($datos['apellido_materno'] ?? '')) : null,
                'razon_social' => !$esDNI ? $datos['razon_social'] : null,
                'direccion' => $datos['direccion'] ?? null,
                'estado' => 'A',
                'fecha_creacion' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al guardar persona: ' . $e->getMessage());
        }
    }
}
