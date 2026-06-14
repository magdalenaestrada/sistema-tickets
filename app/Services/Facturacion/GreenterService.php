<?php

namespace App\Services\Facturacion;

use App\Models\Empresa;
use Greenter\See;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class GreenterService
{
    public function getSee(Empresa $empresa): See
    {
        $see = new See();
        $path = $empresa->certificado_path;

        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception("No existe el certificado en: {$path}");
        }

        $certificado = Storage::disk('public')->get($path);

        $password = $empresa->contrasena_facturacion;

        $see->setCertificate($certificado);

        $see->setService(
            $empresa->modo === 'produccion'
                ? 'https://ose.nubefact.com/ol-ti-itcpe/billService?wsdl'
                : 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService?wsdl'
        );

        $see->setClaveSOL(
            $empresa->documento,
            $empresa->usuario_facturacion,
            $password
        );

        return $see;
    }
}
