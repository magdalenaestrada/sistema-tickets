<?php

namespace App\Enums;

enum EstadoVenta: string
{
    case GENERADO = 'GENERADO';
    case EMITIDO = 'EMITIDO';
    case ANULADO = 'ANULADO';
    
}
