<?php

namespace App\Http\Controllers;

use App\Models\SerieSucursal;
use App\Models\Sucursal;
use App\Models\TipoDocumentoFactura;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SerieSucursalController extends Controller
{
    /**
     * Muestra el listado + el formulario para agregar series.
     */
    public function index()
    {
        $series = SerieSucursal::with(['sucursal', 'tipoDocumentoFactura'])
            ->orderByDesc('id')
            ->get();

        // OJO: ajusta "nombre" si en tus modelos el campo se llama distinto
        $sucursales = Sucursal::orderBy('nombre_comercial')->get();
        $tiposDocumento = TipoDocumentoFactura::orderBy('descripcion')->get();

        return view('series_sucursal.index', compact('series', 'sucursales', 'tiposDocumento'));
    }

    /**
     * Guarda una nueva serie vía AJAX y devuelve JSON
     * para que el JS agregue la fila a la tabla sin recargar.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'tipo_documento_factura_id' => ['required', 'exists:tipo_documentos_factura,id'],
            'serie' => [
                'required',
                'string',
                'max:10',
                Rule::unique('series_sucursal')->where(function ($query) use ($request) {
                    return $query
                        ->where('sucursal_id', $request->sucursal_id)
                        ->where('tipo_documento_factura_id', $request->tipo_documento_factura_id);
                }),
            ],
        ], [
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'sucursal_id.exists' => 'La sucursal seleccionada no es válida.',
            'tipo_documento_factura_id.required' => 'Debe seleccionar el tipo de documento.',
            'tipo_documento_factura_id.exists' => 'El tipo de documento seleccionado no es válido.',
            'serie.required' => 'Debe escribir la serie.',
            'serie.max' => 'La serie no puede tener más de 10 caracteres.',
            'serie.unique' => 'Ya existe esa serie para esta sucursal y tipo de documento.',
        ]);

        $serieSucursal = SerieSucursal::create($validated);
        $serieSucursal->load(['sucursal', 'tipoDocumentoFactura']);

        return response()->json([
            'ok' => true,
            'message' => 'Serie guardada correctamente.',
            'data' => [
                'id' => $serieSucursal->id,
                'sucursal_id' => $serieSucursal->sucursal_id,
                'tipo_documento_factura_id' => $serieSucursal->tipo_documento_factura_id,
                'sucursal' => $serieSucursal->sucursal->nombre_comercial ?? '-',
                'tipo_documento' => $serieSucursal->tipoDocumentoFactura->nombre ?? '-',
                'serie' => $serieSucursal->serie,
            ],
        ]);
    }

    /**
     * Actualiza una serie existente vía AJAX y devuelve JSON
     * para que el JS reemplace la fila en la tabla sin recargar.
     */
    public function update(Request $request, SerieSucursal $serieSucursal)
    {
        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'tipo_documento_factura_id' => ['required', 'exists:tipo_documentos_factura,id'],
            'serie' => [
                'required',
                'string',
                'max:10',
                Rule::unique('series_sucursal')
                    ->ignore($serieSucursal->id)
                    ->where(function ($query) use ($request) {
                        return $query
                            ->where('sucursal_id', $request->sucursal_id)
                            ->where('tipo_documento_factura_id', $request->tipo_documento_factura_id);
                    }),
            ],
        ], [
            'sucursal_id.required' => 'Debe seleccionar una sucursal.',
            'sucursal_id.exists' => 'La sucursal seleccionada no es válida.',
            'tipo_documento_factura_id.required' => 'Debe seleccionar el tipo de documento.',
            'tipo_documento_factura_id.exists' => 'El tipo de documento seleccionado no es válido.',
            'serie.required' => 'Debe escribir la serie.',
            'serie.max' => 'La serie no puede tener más de 10 caracteres.',
            'serie.unique' => 'Ya existe esa serie para esta sucursal y tipo de documento.',
        ]);

        $serieSucursal->update($validated);
        $serieSucursal->load(['sucursal', 'tipoDocumentoFactura']);

        return response()->json([
            'ok' => true,
            'message' => 'Serie actualizada correctamente.',
            'data' => [
                'id' => $serieSucursal->id,
                'sucursal_id' => $serieSucursal->sucursal_id,
                'tipo_documento_factura_id' => $serieSucursal->tipo_documento_factura_id,
                'sucursal' => $serieSucursal->sucursal->nombre_comercial ?? '-',
                'tipo_documento' => $serieSucursal->tipoDocumentoFactura->nombre ?? '-',
                'serie' => $serieSucursal->serie,
            ],
        ]);
    }

    /**
     * (Opcional) Elimina una serie vía AJAX.
     */
    public function destroy(SerieSucursal $serieSucursal)
    {
        $serieSucursal->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Serie eliminada correctamente.',
        ]);
    }
}
