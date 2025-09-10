<?php

namespace App\Services\Consultas;

use App\Interfaces\Consultas\IConsultasApi;

class ConsultasApiService
{
    protected ?IConsultasApi $consulta;

    public function __construct(IConsultasApi $consulta)
    {
        $this->consulta = $consulta;
    }

    public function setConsultaApi(IConsultasApi $consulta)
    {
        $this->consulta = $consulta;
    }

    public function consultarDni(string | int $documento)
    {
        return $this->consulta->consultarDni($documento);
    }

    public function consultarRuc(string | int $documento)
    {
        return $this->consulta->consultarRuc($documento);
    }

    public function consultarAnexosRuc(string | int $documento)
    {
        return $this->consulta->consultarAnexosRuc($documento);
    }

    public function consultarRucConAnexos(string | int $documento)
    {
        return $this->consulta->consultarRucConAnexos($documento);
    }

    public function consultarLicencia(string $documento)
    {
        return $this->consulta->consultarLicencia($documento);
    }
}
