<?php

namespace App\Services\Consultas;

use App\Interfaces\Consultas\IConsultasApi;
use Exception;

/**
 * Class ConsultaBaseService.
 */
class ConsultaBaseService
{
    public function consultar_ruc($service, $documento)
    {
        $api_service = $this->_get_api_services($service);
        return (new ConsultasApiService($api_service))->consultarRuc($documento);
    }

    public function consultar_dni($service, $documento)
    {
        $api_service = $this->_get_api_services($service);
        return (new ConsultasApiService($api_service))->consultarDni($documento);
    }

    public function consultar_anexos_ruc($service, $documento)
    {
        $api_service = $this->_get_api_services($service);
        return (new ConsultasApiService($api_service))->consultarAnexosRuc($documento);
    }

    public function consultar_ruc_con_anexos($service, $documento)
    {
        $api_service = $this->_get_api_services($service);
        return (new ConsultasApiService($api_service))->consultarRucConAnexos($documento);
    }

    public function consultar_licencia($service, $documento)
    {
        $api_service = $this->_get_api_services($service);
        return (new ConsultasApiService($api_service))->consultarLicencia($documento);
    }

    private function _get_api_services(?string $service = null): IConsultasApi | array
    {
        $services = [
            "factiliza" => new ApiFactilizaService(),
            "apisnet" => new ApisNetService(),
        ];
        if ($service) {
            if (!key_exists($service, $services)) {
                throw new Exception("Servicio de {$service} no está implementado aún.", 1);
            }
            return $services[$service];
        }
        return $services;
    }
}
