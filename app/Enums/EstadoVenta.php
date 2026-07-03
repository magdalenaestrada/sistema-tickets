<?php

namespace App\Enums;

enum EstadoVenta: string
{
    case GENERADO = 'GENERADO';
    case EMITIDO = 'EMITIDO';
    case ANULADO = 'ANULADO';
    case ANULADO_CON_NOTA_CREDITO = 'ANULADO_CON_NC';
    case RECHAZADO = 'RECHAZADO';

}
