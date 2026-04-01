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

        // 🔥 CERTIFICADO GLOBAL (NO BD)
        $path = 'certificado/certificate.pem';

        if (!Storage::disk('public')->exists($path)) {
            throw new \Exception("No existe el certificado en: {$path}");
        }

        $certificado = Storage::disk('public')->get($path);

        $password = $empresa->contrasena_facturacion;
        
        $see->setCertificate($certificado);

        $see->setService(
            $empresa->modo === 'produccion'
                ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl'
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
