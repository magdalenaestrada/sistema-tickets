<?php

namespace App\Http\Controllers;

use App\Models\BilleteraDigital;
use App\Models\Pasaje;
use App\Models\Persona;
use App\Models\Horario;
use App\Models\MetodoPago;
use App\Models\TipoDocumentoFactura;
use App\Models\TipoDocumentoPersona;
use App\Services\VentaService;
use App\Services\PagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PasajeController extends Controller
{
    protected $ventaService;
    protected $pagoService;

    public function __construct(VentaService $ventaService, PagoService $pagoService)
    {
        $this->ventaService = $ventaService;
        $this->pagoService = $pagoService;
    }

    public function vender(Request $request)
    {
        $asientos = explode(',', $request->asientos);
        $horario = Horario::with([
            'punto_origen',
            'punto_destino',
            'tipo_vehiculo'
        ])->findOrFail($request->horario);
        $tipos_documentos = TipoDocumentoPersona::all();
        $tipos_documentos_facturas = TipoDocumentoFactura::all();
        $metodos_pago = MetodoPago::all();
        $billeteras_digitales = BilleteraDigital::all();
        return view('pasajes.venta', compact(
            'asientos',
            'horario',
            'tipos_documentos',
            'billeteras_digitales',
            'tipos_documentos_facturas',
            'metodos_pago'
        ));
    }


    public function index()
    {
        $horarios = Horario::with(['tipo_vehiculo', 'punto_origen', 'punto_destino'])
            ->withCount('pasajes')
            ->get();

        return view('pasajes.index', compact('horarios'));
    }


    public function guardar(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'persona.documento' => 'required|string|max:20',
            'persona.nombres' => 'required|string|max:200',
            'asiento_numero' => 'required|integer|min:1|max:15',
            'pasajero_menor' => 'nullable|boolean',
            'autorizacion_pdf' => 'nullable|file|mimes:pdf',
            'total' => 'required|numeric|min:0',
            'pagos' => 'nullable|array'
        ]);

        try {

            $existe = Pasaje::where('horario_id', $request->horario_id)
                ->where('asiento_numero', $request->asiento_numero)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => "El asiento {$request->asiento_numero} ya está ocupado para este horario."
                ], 422);
            }

            $persona = Persona::updateOrCreate(
                ['documento' => $request->input('persona.documento')],
                [
                    'tipo_documento_id' => $request->input('persona.tipo_documento_id', 1),
                    'distrito_id' => $request->input('persona.distrito_id', 1),
                    'nombres' => $request->input('persona.nombres'),
                    'apellidos' => $request->input('persona.apellidos'),
                    'telefono' => $request->input('persona.telefono'),
                    'celular' => $request->input('persona.celular'),
                    'correo' => $request->input('persona.correo'),
                    'direccion' => $request->input('persona.direccion'),
                    'estado' => 'A',
                    'fecha_creacion' => now(),
                ]
            );

            $user_id = Auth::id();

            $pasaje = $this->crearPasaje($request, $persona->id, $user_id);

            return response()->json([
                'success' => true,
                'redirect' => route('pasajes.index'),
                'pasaje_id' => $pasaje->id
            ]);
        } catch (Throwable $th) {
            Log::error('Error al guardar pasaje: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    protected function crearPasaje(Request $request, $personaId, $user_id)
    {
        return DB::transaction(function () use ($request, $personaId, $user_id) {
            $autorizacionPath = null;
            if ($request->hasFile('autorizacion_pdf')) {
                $autorizacionPath = $request->file('autorizacion_pdf')->store('pasajes', 'public');
            }

            $ventaData = $this->ventaService->crearVenta(
                $request->all(),
                Pasaje::class,
                null
            );
            dd($ventaData);

            $pasaje = Pasaje::create([
                'usuario_id' => $user_id,
                'persona_id' => $personaId,
                'horario_id' => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'pasajero_menor' => $request->pasajero_menor ?? false,
                'autorizacion_pdf' => $autorizacionPath,
                'estado' => 'A',
                'fecha_creacion' => now(),
                'venta_id' => $ventaData['venta']->id,
            ]);

            $this->pagoService->registrarPagos(
                $ventaData['venta']->id,
                $request->pagos ?? [],
                Pasaje::class,
                $pasaje->id
            );

            return $pasaje;
        });
    }

    public function asientosHorario(Horario $horario)
    {
        $pasajes = $horario->pasajes()->get();
        $asientos = [];
        $precios = [];
        $precioPorAsiento = $horario->costo_pasaje ?? 0;
        for ($i = 1; $i <= $horario->tipo_vehiculo->capacidad; $i++) {
            $pasaje = $pasajes->firstWhere('asiento_numero', $i);

            if ($pasaje) {
                $asientos[$i] = $pasaje->pasajero_menor ? 'reservado' : 'ocupado';
            } else {
                $asientos[$i] = 'libre';
            }

            $precios[$i] = $precioPorAsiento;
        }

        $rawSvg = file_get_contents(storage_path('app/public/' . $horario->tipo_vehiculo->ruta_svg));

        $cleanSvg = preg_replace('/<\\?xml.*?\\?>/is', '', $rawSvg);
        $cleanSvg = preg_replace('/<!DOCTYPE.*?>/is', '', $cleanSvg);

        return response()->json([
            'asientos' => $asientos,
            'precios' => $precios,
            'svg' => $cleanSvg
        ]);
    }

    public function reservar(Request $request)
    {
        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'asiento_numero' => 'required|integer|min:1',
        ]);

        try {
            $existe = Pasaje::where('horario_id', $request->horario_id)
                ->where('asiento_numero', $request->asiento_numero)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => "El asiento {$request->asiento_numero} ya está ocupado."
                ], 422);
            }

            $persona = null;
            if ($request->has('persona.documento')) {
                $persona = Persona::updateOrCreate(
                    ['documento' => $request->input('persona.documento')],
                    [
                        'tipo_documento_id' => $request->input('persona.tipo_documento_id', 1),
                        'distrito_id' => $request->input('persona.distrito_id', 1),
                        'nombres' => $request->input('persona.nombres'),
                        'apellidos' => $request->input('persona.apellidos'),
                        'telefono' => $request->input('persona.telefono'),
                        'celular' => $request->input('persona.celular'),
                        'correo' => $request->input('persona.correo'),
                        'direccion' => $request->input('persona.direccion'),
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );
            }

            $pasaje = Pasaje::create([
                'usuario_id' => Auth::id(),
                'horario_id' => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'persona_id' => $persona ? $persona->id : null,
                'estado' => 'A',
                'fecha_creacion' => now(),
                'venta_id' => null, 
            ]);


            return response()->json([
                'success' => true,
                'pasaje_id' => $pasaje->id,
                'asiento_numero' => $request->asiento_numero
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
