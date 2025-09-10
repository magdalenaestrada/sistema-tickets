<?php

namespace App\Http\Controllers\Consultas;

use App\Enums\Response\ResponseStatusEnum;
use App\Http\Controllers\Controller;
use App\Services\Consultas\ConsultaBaseService;
use Exception;

class ConsultaController extends Controller
{
    public function consultar_ruc($service, $documento, ConsultaBaseService $consultaBaseService)
    {
        try {
            $empresa = $consultaBaseService->consultar_ruc($service, $documento);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "empresa" => $empresa,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
            ]);
        }
    }
    public function consultar_dni($service, $documento, ConsultaBaseService $consultaBaseService)
    {
        try {
            $persona = $consultaBaseService->consultar_dni($service, $documento);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "persona" => $persona,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
            ]);
        }
    }
    public function consultar_anexos_ruc($service, $documento, ConsultaBaseService $consultaBaseService)
    {
        try {
            $anexos = $consultaBaseService->consultar_anexos_ruc($service, $documento);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "anexos" => $anexos,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
            ]);
        }
    }
    public function consultar_ruc_con_anexos($service, $documento, ConsultaBaseService $consultaBaseService)
    {
        try {
            $empresa = $consultaBaseService->consultar_ruc_con_anexos($service, $documento);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "empresa" => $empresa,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
            ]);
        }
    }
    public function consultar_licencia($service, $documento, ConsultaBaseService $consultaBaseService)
    {
        try {
            $persona = $consultaBaseService->consultar_licencia($service, $documento);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "persona" => $persona,
            ]);
        } catch (Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
            ]);
        }
    }
}
