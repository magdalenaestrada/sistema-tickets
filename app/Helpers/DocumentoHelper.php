<?php

namespace App\Helpers;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class DocumentoHelper
{
    public static function generar_documento(string $plantilla, array $data = [], string $paper = "A4", array $configOverride = [])
    {
        $empresa = empresa();
        $logo = $empresa->logo;
        $configuracionPapel = self::obtener_configuracion_por_papel($paper, $configOverride);
        return PDF::loadView(
            $plantilla,
            $data,
            [
                "fecha_hoy" => now()->format("d/m/Y"),
                "hora_hoy" => now()->format("H:i:s"),
                "usuario" => auth_user()->name,
                "logo" => public_path($logo),
                "empresa" => $empresa,
            ],
            $configuracionPapel
        );
    }
    protected static function obtener_configuracion_por_papel(string $paper = "A4", array $configOverride = []): array
    {
        if ($paper === "TK") {
            return self::obtener_configuracion_ticket($configOverride);
        }
        // mas papeles ...
        return self::obtener_configuracion_a4($configOverride);
    }

    //devuelve una configuracion especifica para el papel A4 a MPDF
    public static function obtener_configuracion_a4(array $configOverride = []): array
    {
        return array_merge([
            'mode' => 'utf-8',
            'format' => 'A4-P',
            'orientation' => 'P',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 27,
            'margin_bottom' => 20,
            'margin_header' => 5,
            'margin_footer' => 5,
            'custom_font_dir' => public_path('assets/fonts/'), // don't forget the trailing slash!
            'custom_font_data' => [
                'roboto' => [ // must be lowercase and snake_case
                    'R' => 'RobotoMono-Regular.ttf', // regular font
                    'B' => 'RobotoMono-Bold.ttf', // optional: bold font
                    'I' => 'RobotoMono-BoldItalic.ttf', // optional: italic font
                    'BI' => 'RobotoMono-Italic.ttf', // optional: bold-italic font
                ],
            ],
        ], $configOverride);
    }

    //devuelve una configuracion especifica para el TICKET (80mm) papel a MPDF
    public static function obtener_configuracion_ticket(array $configOverride = []): array
    {
        return array_merge([
            'mode' => 'utf-8',
            'format' => [80, 270],
            'orientation' => 'P',
            'margin_left' => 3,
            'margin_right' => 3,
            'margin_header' => 0,
            'margin_footer' => 0,
            'custom_font_dir' => public_path('assets/fonts/'), // don't forget the trailing slash!
            'custom_font_data' => [
                'roboto' => [ // must be lowercase and snake_case
                    'R' => 'RobotoMono-Regular.ttf', // regular font
                    'B' => 'RobotoMono-Bold.ttf', // optional: bold font
                    'I' => 'RobotoMono-BoldItalic.ttf', // optional: italic font
                    'BI' => 'RobotoMono-Italic.ttf', // optional: bold-italic font
                ],
            ],
        ], $configOverride);
    }
}
