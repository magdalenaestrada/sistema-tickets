<?php

namespace App\Services\Consultas;

use App\DTOs\ConductorLicenciaDTO;
use App\DTOs\EmpresaConsultaDTO;
use App\DTOs\EstablecimientoAnexoDTO;
use App\DTOs\PersonaConsultaDTO;
use App\Interfaces\Consultas\IConsultasApi;
use App\Traits\ApiTokenHandler;
use Illuminate\Support\Facades\Http;

/**
 * Class ConductorService.
 */
class ApisNetService implements IConsultasApi
{
    use ApiTokenHandler;

    protected $token;
    protected $apiUrl;

    public function consultarDni(string|int $documento)
    {
        $this->token = $this->getToken('apisnet');
        $this->apiUrl = $this->getApiUrl('apisnet');
        $response = Http::withToken($this->token)
            ->get("{$this->apiUrl}/reniec/dni?numero={$documento}");
        $responseJson = $response->json();
        if ($response->failed()) {
            throw new \Exception("Error {$response->status()} : {$responseJson['message']}");
        }
        return PersonaConsultaDTO::fromApisNet($responseJson)->toArray();
    }

    public function consultarRuc(string|int $documento)
    {
        $this->token = $this->getToken('apisnet');
        $this->apiUrl = $this->getApiUrl('apisnet');
        $response = Http::withToken($this->token)
            ->get("{$this->apiUrl}/sunat/ruc?numero={$documento}");
        $responseJson = $response->json();
        if ($response->failed()) {
            throw new \Exception("Error {$response->status()} : {$responseJson['message']}");
        }
        return EmpresaConsultaDTO::fromApisNet($responseJson)->toArray();
    }

    public function consultarAnexosRuc(string|int $documento)
    {
        throw new \Exception("Servicio de anexos no está implementado aún.", 1);
        // $this->token = $this->getToken('apisnet');
        // $this->apiUrl = $this->getApiUrl('apisnet');
        // $response = Http::withToken($this->token)
        //     ->get("{$this->apiUrl}/ruc/anexo/{$documento}");
        // $responseJson = $response->json();
        // if (empty($responseJson['data']) || !is_array($responseJson['data'])) {
        //     return [];
        // }
        // if ($response->failed()) {
        //     throw new \Exception("Error {$response->status()} : {$responseJson['message']}");
        // }
        // return array_map(
        //     fn($establecimiento) => EstablecimientoAnexoDTO::fromFactiliza($establecimiento)->toArray(),
        //     $responseJson["data"]
        // );
    }

    public function consultarRucConAnexos(string|int $documento)
    {
        throw new \Exception("Servicio de anexos no está implementado aún.", 1);
        // $empresa = $this->consultarRuc($documento);
        // $anexos = $this->consultarAnexosRuc($documento);
        // return [
        //     "empresa" => $empresa,
        //     "anexos" => $anexos,
        // ];
    }

    public function consultarLicencia(string $documento)
    {
        throw new \Exception("Servicio de licencia no está implementado aún.", 1);
        // $this->token = $this->getToken('factiliza');
        // $this->apiUrl = $this->getApiUrl('factiliza');
        // $response = Http::withToken($this->token)
        //     ->get("{$this->apiUrl}/licencia/info/{$documento}");
        // $responseJson = $response->json();
        // if ($response->failed()) {
        //     throw new \Exception("Error {$response->status()} : {$responseJson['message']}");
        // }
        // return ConductorLicenciaDTO::fromFactiliza($responseJson["data"])->toArray();
    }
}
