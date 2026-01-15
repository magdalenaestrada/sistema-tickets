<?php

namespace App\Http\Controllers;

use App\Models\Persona;
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

        if (!in_array(strlen($numero), [8, 11])) {
            return response()->json(['error' => 'Número de documento inválido.'], 400);
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
        $token   = config('token.decolecta.token');
        $baseUrl = config('token.decolecta.url');

        if (strlen($numero) === 8) {
            $endpoint = "reniec/dni?numero={$numero}";
        } else {
            $endpoint = "sunat/ruc?numero={$numero}";
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
        ])->get($baseUrl . $endpoint);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (strlen($numero) === 8) {
            return [
                'tipo' => 'DNI',
                'documento' => $data['document_number'] ?? $numero,
                'nombres' => $data['first_name'] ?? '',
                'apellido_paterno' => $data['first_last_name'] ?? '',
                'apellido_materno' => $data['second_last_name'] ?? '',
                'nombre_completo' => $data['full_name'] ?? '',
                'origen' => 'api_externa',
            ];
        } else {
            return [
                'tipo' => 'RUC',
                'documento' => $data['numero_documento'] ?? $numero,
                'razon_social' => $data['razon_social'] ?? '',
                'direccion' => $data['direccion'] ?? '',
                'estado' => $data['estado'] ?? '',
                'condicion' => $data['condicion'] ?? '',
                'departamento' => $data['departamento'] ?? '',
                'provincia' => $data['provincia'] ?? '',
                'distrito' => $data['distrito'] ?? '',
                'origen' => 'api_externa',
            ];
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
