<?php

namespace App\Http\Controllers\Empresa;

use App\Enums\Response\ResponseStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Empresas\SubmitEmpresasRequest;
use App\Services\EmpresasService;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function data_table(EmpresasService $service, $empresa)
    {
        try {
            if (!request()->ajax()) {
                throw new \Exception("Solo se permiten consultas por AJAX.");
            }
            return $service->datatable()->toJson();
        } catch (\Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage(),
                "data" => []
            ]);
        }
    }

    public function guardar($empresa, SubmitEmpresasRequest $request, EmpresasService $service)
    {
        try {
            if (!$request->ajax()) {
                throw new \Exception("Solo se permiten consultas por AJAX.");
            }
            $empresa = $service->guardar($empresa, $request);
            return response()->json([
                "status" => ResponseStatusEnum::SUCCESS,
                "empresa" => $empresa
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                "status" => ResponseStatusEnum::ERROR,
                "message" => $exception->getMessage()
            ]);
        }
    }
}
