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
use Illuminate\Validation\ValidationException;
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

    protected function validarTerminarVenta($request, $i)
    {
        $rules = [
            "tipo_documento_id.$i" => 'required|integer',
            "documento.$i" => 'required|string|max:20',
            "nombres.$i" => 'required|string|max:200',
            "apellidos.$i" => 'required|string|max:200',
            "celular.$i" => 'required|string',
            "pago_efectivo.$i" => 'nullable|numeric|min:0',
            "pago_billetera.$i" => 'nullable|numeric|min:0',
        ];

        $request->validate($rules);
    }

    protected function crearPasajeMultiple(Request $request, $personaId, $i, $estado)
    {

        $asiento = $request->asientos[$i];
        $horario_id = $request->horario_id[$i];

        $existe = Pasaje::where('horario_id', $horario_id)
            ->where('asiento_numero', $asiento)
            ->exists();

        if ($existe) {
            throw new \Exception("El asiento $asiento ya está ocupado.");
        }

        $pdf = null;
        if ($request->hasFile("autorizacion_pdf.$i")) {
            $pdf = $request->file("autorizacion_pdf.$i")->store('pasajes', 'public');
        }

        $ventaData = null;
        $venta_id = null;

        if ($estado === 'V') {
            $horario = Horario::with(['punto_origen', 'punto_destino'])->findOrFail($horario_id);
            $precioPasaje = $horario->costo_pasaje ?? 0;
            $descuento = $request->descuento[$i] ?? 0;

            // Usar el método específico para pasajes
            $ventaData = $this->ventaService->crearVentaPasaje(
                $horario,
                $asiento,
                $precioPasaje,
                $descuento,
                $request->tipo_documento_factura_id ?? 1
            );

            $venta_id = $ventaData['venta']->id;
        }

        $pasaje = Pasaje::create([
            'usuario_id'      => Auth::id(),
            'persona_id'      => $personaId,
            'horario_id'      => $horario_id,
            'asiento_numero'  => $asiento,
            'pasajero_menor'  => isset($request->pasajero_menor[$i]) ? true : false,
            'autorizacion_pdf' => $pdf,
            'venta_id'        => $venta_id,
            'estado'          => $estado,
            'fecha_creacion'  => now(),
        ]);

        if ($ventaData) {
            $pagoData = [];

            // Normalizamos valores antes de floatval
            $pagoEfectivo = floatval(str_replace(',', '.', $request->pago_efectivo[$i] ?? 0));
            $pagoBilletera = floatval(str_replace(',', '.', $request->pago_billetera[$i] ?? 0));

            if ($pagoEfectivo > 0) {
                $pagoData[] = [
                    'metodo_pago_id' => 1,
                    'total' => $pagoEfectivo,
                    'billetera_id' => null
                ];
            }

            if ($pagoBilletera > 0) {
                $pagoData[] = [
                    'metodo_pago_id' => 2,
                    'total' => $pagoBilletera,
                    'billetera_id' => $request->billetera_id[$i] ?? null
                ];
            }

            $this->pagoService->registrarPagos(
                $ventaData['venta']->id,
                $pagoData,
                Pasaje::class,
                $pasaje->id
            );
        }


        return $pasaje;
    }

    public function guardar(Request $request)
    {
        $accion = $request->accion; // reservar | terminar

        Log::info('Guardando pasajes', [
            'accion' => $accion,
            'asientos' => $request->asientos
        ]);

        try {
            DB::beginTransaction();

            $asientos = $request->asientos;

            $estado = ($accion === 'terminar') ? 'V' : 'R';

            foreach ($asientos as $index => $asiento) {

                if ($accion === 'terminar') {
                    $this->validarTerminarVenta($request, $index);
                }

                $persona = Persona::updateOrCreate(
                    ['documento' => $request->documento[$index] ?? null],
                    [
                        'tipo_documento_id' => $request->tipo_documento_id[$index] ?? 1,
                        'nombres' => $request->nombres[$index] ?? null,
                        'apellidos' => $request->apellidos[$index] ?? null,
                        'telefono' => $request->telefono[$index] ?? null,
                        'celular' => $request->celular[$index] ?? null,
                        'correo' => $request->direccion[$index] ?? null,
                        'fecha_creacion' => now(),

                    ]
                );

                Log::info("Creando pasaje para asiento {$asiento}", [
                    'estado' => $estado,
                    'persona_id' => $persona->id
                ]);

                $this->crearPasajeMultiple($request, $persona->id, $index, $estado);
            }

            DB::commit();

            Log::info('Pasajes guardados exitosamente', ['estado' => $estado]);

            return response()->json([
                'success' => true,
                'message' => $estado === 'V' ? 'Venta realizada correctamente' : 'Reserva realizada correctamente',
                'redirect' => route('pasajes.index')
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Error al guardar pasajes', [
                'message' => $th->getMessage(),
                'line' => $th->getLine()
            ]);

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

            $pasaje = Pasaje::create([
                'usuario_id' => $user_id,
                'persona_id' => $personaId,
                'horario_id' => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'pasajero_menor' => $request->pasajero_menor ?? false,
                'autorizacion_pdf' => $autorizacionPath,
                'estado' => 'R',
                'fecha_creacion' => now(),
                'venta_id' => $ventaData['venta']->id,
            ]);

            if ($ventaData) {
                $pagoData = [];

                // Normalizamos valores antes de floatval
                $pagoEfectivo = floatval(str_replace(',', '.', $request->pago_efectivo ?? 0));
                $pagoBilletera = floatval(str_replace(',', '.', $request->pago_billetera ?? 0));

                if ($pagoEfectivo > 0) {
                    $pagoData[] = [
                        'metodo_pago_id' => 1,
                        'total' => $pagoEfectivo,
                        'billetera_id' => null
                    ];
                }

                if ($pagoBilletera > 0) {
                    $pagoData[] = [
                        'metodo_pago_id' => 2,
                        'total' => $pagoBilletera,
                        'billetera_id' => $request->billetera_id ?? null
                    ];
                }

                $this->pagoService->registrarPagos(
                    $ventaData['venta']->id,
                    $pagoData,
                    Pasaje::class,
                    $pasaje->id
                );
            }
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
                switch ($pasaje->estado) {
                    case 'R':
                        $asientos[$i] = 'reservado';
                        break;
                    case 'V':
                        $asientos[$i] = 'ocupado';
                        break;
                    default:
                        $asientos[$i] = 'ocupado';
                }
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
        // Log para debugging
        Log::info('Datos recibidos en reservar:', $request->all());

        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'asiento_numero' => 'required|integer|min:1',
            // Validaciones opcionales de persona
            'persona.documento' => 'nullable|string|max:20',
            'persona.nombres' => 'nullable|string|max:200',
            'persona.apellidos' => 'nullable|string|max:200',
        ]);

        try {
            // Verificar que el asiento no esté ocupado
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

            // Crear persona solo si hay datos
            if ($request->has('persona.documento') && !empty($request->input('persona.documento'))) {
                $persona = Persona::updateOrCreate(
                    ['documento' => $request->input('persona.documento')],
                    [
                        'tipo_documento_id' => $request->input('persona.tipo_documento_id', 1),
                        'nombres' => $request->input('persona.nombres'),
                        'apellidos' => $request->input('persona.apellidos'),
                        'telefono' => $request->input('persona.telefono'),
                        'celular' => $request->input('persona.celular'),
                        'correo' => $request->input('persona.correo'),
                        'estado' => 'A',
                        'fecha_creacion' => now(),
                    ]
                );

                Log::info("Persona creada/actualizada:", ['id' => $persona->id, 'documento' => $persona->documento]);
            } else {
                Log::info("Reserva sin datos de persona");
            }

            // Crear el pasaje (reserva)
            $pasaje = Pasaje::create([
                'usuario_id' => Auth::id(),
                'horario_id' => $request->horario_id,
                'asiento_numero' => $request->asiento_numero,
                'persona_id' => $persona ? $persona->id : null,
                'pasajero_menor' => false, // Las reservas no tienen esta info aún
                'estado' => 'R',
                'fecha_creacion' => now(),
                'venta_id' => null, // Las reservas no tienen venta asociada
            ]);

            Log::info("Pasaje (reserva) creado:", ['id' => $pasaje->id, 'asiento' => $pasaje->asiento_numero]);

            return response()->json([
                'success' => true,
                'pasaje_id' => $pasaje->id,
                'asiento_numero' => $request->asiento_numero,
                'message' => 'Asiento reservado correctamente'
            ]);
        } catch (ValidationException $e) {
            Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (Throwable $th) {
            Log::error('Error al reservar:', [
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al reservar: ' . $th->getMessage()
            ], 500);
        }
    }
}
