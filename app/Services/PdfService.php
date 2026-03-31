<?php

namespace App\Services;

use Mpdf\Mpdf;

class PdfService
{
    public function generar($html, $nombre = 'documento.pdf', $orientacion = 'L', $modo = 'D')
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientacion,
            'margin_top' => 6,
            'margin_bottom' => 6,
            'margin_left' => 6,
            'margin_right' => 6,
        ]);

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetTitle($nombre);
        $mpdf->WriteHTML($html);

        return $mpdf->Output($nombre, 'I');
    }
}
