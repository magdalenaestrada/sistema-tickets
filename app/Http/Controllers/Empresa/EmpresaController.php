<?php

namespace App\Http\Controllers\Empresa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Empresas\SubmitEmpresasRequest;
use App\Models\Empresas;
use App\Services\Empresas\EmpresasService;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function __construct(protected EmpresasService $service) {}

    public function index()
    {
        return view('empresas.index');
    }

    public function datatable()
    {
        return $this->service->datatable()->make(true);
    }

    public function guardar(SubmitEmpresasRequest $request)
    {
        $empresa = $this->service->guardar($request);
        return response()->json($empresa);
    }

    public function actualizar(SubmitEmpresasRequest $request, $id)
    {
        $empresa = $this->service->editar($id, $request);
        return response()->json($empresa);
    }

    public function activar(Empresas $empresa)
    {
        $this->service->activar($empresa);
        return response()->json(['message' => 'Empresa activada']);
    }

    public function desactivar(Empresas $empresa)
    {
        $this->service->desactivar($empresa);
        return response()->json(['message' => 'Empresa desactivada']);
    }
}
