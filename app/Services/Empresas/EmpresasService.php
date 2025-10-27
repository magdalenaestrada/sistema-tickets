<?php

namespace App\Services\Empresas;

use App\Enums\Models\ModelStatusEnum;
use App\Helpers\HTMLHelper;
use App\Http\Requests\Empresas\SubmitEmpresasRequest;
use App\Models\Empresas;
use Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

/**
 * Class EmpresasService.
 */
class EmpresasService
{
    public function busqueda_global(array $search)
    {
        return Cache::remember(isset($search["cache_name"]) ? $search["cache_name"] : 'busqueda_global_empresas', isset($search["cache_time"]) ?: 3600, function () use ($search) {
            return Empresas::when(isset($search["with"]), function ($query) use ($search) {
                foreach ($search["with"] as $with) {
                    $query->with($with);
                }
            })->when(isset($search["search"]), function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where("razon_social", "like", $search["search"] . "%")
                        ->orWhere("nombre_comercial", "like", $search["search"] . "%")
                        ->orWhere("documento", "like", $search["search"] . "%");
                });
            })->when(isset($search["where"]), function ($query) use ($search) {
                foreach ($search["where"] as $whereEqual) {
                    $query->where($whereEqual["column"], $whereEqual["operator"], $whereEqual["value"]);
                }
            })->when(isset($search["limit"]), function ($query) use ($search) {
                $query->limit($search["limit"]);
            })->when(isset($search["id"]), function ($query) use ($search) {
                $query->where("id", $search["id"]);
            })->get()->toArray();
        });
    }

    public function datatable()
    {
        $model = Empresas::orderByDesc("id");
        return DataTables::of($model)
            ->addIndexColumn()
            ->only([
                "documento",
                "razon_social",
                "nombre_comercial",
                "direccion",
                "usuario_facturacion",
                "contrasena_facturacion",
                "estado",
                "opciones"
            ])->addColumn('estado', function ($model) {
                $estado = ModelStatusEnum::from($model->estado);
                $color = $estado === ModelStatusEnum::INACTIVO ? 'danger' : 'success';
                return HTMLHelper::badge($estado, $color);
            })->addColumn('opciones', function ($model) {
                $is_delete = ModelStatusEnum::from($model->estado) === ModelStatusEnum::INACTIVO;
                $menu = [
                    "edit" => [
                        "element" => "a",
                        "href" => "#!",
                        "class" => "btn-xs pt-1 btn-warning editarEmpresa",
                        "text" => '<i class="fa-solid fa-pen"></i>',
                        "data" => [
                            "id" => $model->id,
                        ],
                    ],

                    "desactivate" => [
                        "element" => "a",
                        "href" => "#!",
                        "text" => '<i class="fa-solid fa-eye-slash"></i>',
                        "class" => "btn-xs pt-1 btn-danger activarDesactivarEmpresa",
                        "data" => [
                            "action" => route("#", ["empresa" => $model->id]),
                            "type-action" => "0",
                        ],
                    ],
                    "activate" => [
                        "element" => "a",
                        "text" => '<i class="fa-solid fa-eye"></i>',
                        "href" => "#!",
                        "class" => "btn-xs pt-1 btn-success activarDesactivarEmpresa",
                        "data" => [
                            "action" => route("#", ["empresa" => $model->id]),
                            "type-action" => "1",
                        ],
                    ],
                ];
                $btnEditar = "";
                $btnDesactivar = "";
                $btnActivar = "";
                if (!$is_delete) {
                    $btnEditar = HTMLHelper::generarButton($menu["edit"]);
                    $btnDesactivar = HTMLHelper::generarButton($menu["desactivate"]);
                } else {
                    $btnActivar = HTMLHelper::generarButton($menu["activate"]);
                }
                return "{$btnEditar} {$btnDesactivar} {$btnActivar}";
            })->rawColumns(['estado', 'opciones']);
    }

    public function guardar(SubmitEmpresasRequest $request)
    {
        $insert = [
            "documento" => $request->documento,
            "razon_social" => $request->razon_social,
            "nombre_comercial" => $request->nombre_comercial,
            "direccion" => $request->direccion,
            "usuario_facturacion" => $request->usuario_facturacion,
            "contrasena_facturacion" => $request->contrasena_facturacion,
        ];
        try {

            $cuenta = DB::transaction(function () use ($insert) {
                return Empresas::create($insert);
            });

            activity()
                ->performedOn($cuenta)
                ->withProperties($insert)
                ->log("Se insertó una fila en empresas");

            return $cuenta;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function editar(string|int $empresa_id, SubmitEmpresasRequest $request)
    {
        $update = [
            "documento" => $request->documento,
            "razon_social" => $request->razon_social,
            "nombre_comercial" => $request->nombre_comercial,
            "direccion" => $request->direccion,
            "usuario_facturacion" => $request->usuario_facturacion,
            "contrasena_facturacion" => $request->contrasena_facturacion,
        ];
        try {
            $empresa = DB::transaction(function () use ($update, $empresa_id) {
                Empresas::where("id", $empresa_id)->update($update);
                return Empresas::findOrFail($empresa_id);
            });
            activity()
                ->performedOn($empresa)
                ->log('Se editó una empresa');

            return $empresa;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    public function desactivar(Empresas $empresa)
    {
        if ($empresa->sucursales()->exists() || $empresa->usuarios()->exists()) {
            throw new \Exception("No se puede desactivar la empresa porque tiene registros relacionados.");
        }

        $empresa->estado = ModelStatusEnum::INACTIVO;
        $empresa->save();

        activity()
            ->performedOn($empresa)
            ->log("Se desactivó una empresa");
    }

    public function activar(Empresas $empresa)
    {
        $empresa->estado = ModelStatusEnum::ACTIVO;
        $empresa->save();

        activity()
            ->performedOn($empresa)
            ->log("Se activó una empresa");
    }
}
