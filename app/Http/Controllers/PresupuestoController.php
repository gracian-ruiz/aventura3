<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presupuesto;
use App\Models\User;
use App\Models\Component;
use App\Models\AppointmentComponent;
use App\Models\Bike;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Appointment;
use Carbon\Carbon;


class PresupuestoController extends Controller
{
    private function buildIndexContextFromRequest(Request $request): array
    {
        $context = [
            'page' => $request->input('return_page', $request->query('page')),
            'search' => $request->input('return_search', $request->query('search')),
            'origen' => $request->input('return_origen', $request->query('origen')),
            'prioridad' => $request->input('return_prioridad', $request->query('prioridad')),
        ];

        return array_filter($context, static fn($value) => $value !== null && $value !== '');
    }

public function index(Request $request)
{
    $search = $request->input('search');
    $filtroOrigen = $request->input('origen'); // 'web' o 'tienda'
    $filtroPrioridad = $request->input('prioridad'); // 'premium', 'urgente', 'normal'

    $presupuestos = DB::table('appointments')
        ->leftJoin('bikes', 'appointments.bike_id', '=', 'bikes.id')
        ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
        ->select(
            'appointments.*',
            'bikes.nombre as bike_nombre',
            'bikes.marca as bike_marca',
            'users.name as user_nombre',
            'users.role as user_role'
        )
        ->whereIn('appointments.estado', ['presupuesto', 'denegado', 'vacia'])
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('bikes.nombre', 'like', "%{$search}%")
                    ->orWhere('bikes.marca', 'like', "%{$search}%")
                    ->orWhere('appointments.idprograma', 'like', "%{$search}%");
            });
        })
        // 🔹 Filtro por origen (web/tienda)
        ->when($filtroOrigen === 'web', function ($query) {
            $query->where('appointments.web', 1);
        })
        ->when($filtroOrigen === 'tienda', function ($query) {
            $query->where('appointments.web', 0);
        })
        // 🔹 Filtro por prioridad
        ->when($filtroPrioridad, function ($query, $filtroPrioridad) {
            $query->where('appointments.prioridad', $filtroPrioridad);
        })
        ->orderByRaw('
            CASE 
                WHEN appointments.prioridad = "premium" AND appointments.web = 1 THEN 0
                WHEN appointments.prioridad = "premium" THEN 1
                WHEN appointments.prioridad = "urgente" THEN 2
                ELSE 3
            END
        ')
        ->orderBy('appointments.created_at', 'asc')
        ->paginate(10)
        ->appends([
            'search' => $search,
            'origen' => $filtroOrigen,
            'prioridad' => $filtroPrioridad
        ]);

    return view('presupuestos.index', compact('presupuestos', 'search', 'filtroOrigen', 'filtroPrioridad'));
}


    public function create($userId)
    {
        $user = User::findOrFail($userId);
        $bikes = Bike::where('user_id', $userId)->get();
        $components = Component::all();

        // Obtener usuarios con rol 'admin' o 'taller'
        $talleristas = User::whereIn('role', ['admin', 'taller'])->get();

        return view('presupuestos.create', compact('user', 'bikes', 'components', 'talleristas'));
    }



    public function store(Request $request)
    {
        $hasPrecioMaterial = Schema::hasColumn('appointment_component', 'precio_material');

        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'nullable|array',
            'horas_trabajo' => 'nullable|array',
            'precios' => 'nullable|array',
            'precio_materiales' => 'nullable|array',
            'textos' => 'nullable|array',
            'descuentos' => 'nullable|array',

            'horas_trabajo.*' => 'nullable|integer|min:0',
            'precios.*' => 'nullable|numeric|min:0',
            'precio_materiales.*' => 'nullable|numeric|min:0',
            'idprograma*' => 'nullable',
            'descuentos.*' => 'nullable|integer|min:0',
            'asignacion_taller' => 'nullable|array',
            'asignacion_taller.*' => 'exists:users,id',
        ]);

        $bikeId = $request->bike_id;
        $componentes = $request->componentes ?? [];
        $horasTrabajo = $request->horas_trabajo ?? [];
        $precios = $request->precios ?? [];
        $precioMateriales = $request->precio_materiales ?? [];
        $textos = $request->textos ?? [];
        $descuentos = $request->descuentos ?? [];

        if (
            count($componentes) > 0 &&
            (count($componentes) !== count($horasTrabajo) ||
                count($componentes) !== count($precios) ||
                count($componentes) !== count($precioMateriales) ||
                count($componentes) !== count($textos))
        ) {
            return redirect()->back()->withErrors(['error' => 'Los datos de los componentes no coinciden.']);
        }

        $bike = Bike::find($bikeId);
        if (!$bike) {
            return redirect()->back()->withErrors(['error' => 'Bicicleta no encontrada.']);
        }

        // 🔒 SEGURIDAD: Token criptográficamente seguro (64 caracteres aleatorios)
        $tokenPresupuesto = \Illuminate\Support\Str::random(64);

        try {
            $presupuesto = Appointment::create([
                'bike_id' => $bikeId,
                'horas_total' => 0,
                'precio_total' => 0,
                'user_id' => $bike->user_id,
                'token_presupuesto' => $tokenPresupuesto,
                'mensaje_enviado' => false,
                'prioridad' => $request->prioridad,
                'estimacion_reparacion' => '',
                'estado' => count($componentes) > 0 ? 'presupuesto' : 'vacía',
                'descuento' => 0,
                'asignacion_taller' => $request->asignacion_taller ?? [],
                'idprograma' => $request->idprograma,
                'calendario' => $request->calendario,
            ]);

            $totalHoras = 0;
            $totalPrecio = 0;
            $totalDescuento = 0;

            if (count($componentes) > 0) {
                foreach ($componentes as $index => $componenteId) {
                    $horas = (int) $horasTrabajo[$index];
                    $precioManoObra = (float) $precios[$index];
                    $precioMaterial = (float) ($precioMateriales[$index] ?? 0);
                    $texto = $textos[$index] ?? '';
                    $descuento = isset($descuentos[$index]) ? (int) $descuentos[$index] : 0;
                    $precioBruto = $precioManoObra + $precioMaterial;

                    $itemData = [
                        'appointment_id' => $presupuesto->id,
                        'componente_id' => $componenteId,
                        'horas_trabajo' => $horas,
                        'total_precio' => $precioManoObra,
                        'texto' => $texto,
                        'descuento' => $descuento,
                    ];

                    if ($hasPrecioMaterial) {
                        $itemData['precio_material'] = $precioMaterial;
                    }

                    AppointmentComponent::create($itemData);

                    $totalHoras += $horas;
                    $totalPrecio += max($precioBruto - $descuento, 0);
                    $totalDescuento += $descuento;
                }

                $presupuesto->update([
                    'horas_total' => $totalHoras,
                    'tiempo_reparacion' => $totalHoras,
                    'precio_total' => round($totalPrecio, 2),
                    'descuento' => $totalDescuento,
                ]);
            }

            return redirect()->route('presupuestos.factura', ['id' => $presupuesto->id])
                ->with('success', 'Presupuesto guardado correctamente.');
        } catch (\Exception $e) {
            Log::error('[PresupuestoController] Error al crear presupuesto', [
                'bike_id' => $bikeId,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Error al guardar el presupuesto. Inténtalo de nuevo.');
        }
    }



    public function factura(Request $request, $id)
    {
        // Obtener el presupuesto con la bicicleta y el usuario
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.id', $id)
            ->select(
                'appointments.*',
                'bikes.id as bicicleta_id',
                'bikes.nombre as bicicleta_nombre',
                'users.id as usuario_id',
                'users.name as usuario_nombre',
                'users.email as usuario_email'
            )
            ->first();

        if (!$presupuesto) {
            abort(404, 'Presupuesto no encontrado');
        }

        // Obtener los ítems del presupuesto
        $items = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $id)
            ->select([
                'appointment_component.*',
                'components.nombre as componente_nombre'
            ])
            ->get();

        $iva = 21;

        // Construcción del link con token
        $presupuestoId = $presupuesto->id;
        $presupuestoUrl = url("confirmacion/presupuesto/{$presupuestoId}?token={$presupuesto->token_presupuesto}");

        // Crear el mensaje para enviar al cliente
        $mensaje = "📄 ¡Hola {$presupuesto->usuario_nombre}! Te escribo de Aventura Bike, te envío el presupuesto para arreglar tu bicicleta '{$presupuesto->bicicleta_nombre}'.\n\n"
            . "📎 Adjuntamos el PDF con los detalles.\n\n"
            . "🔗 Puedes confirmar el presupuesto pinchando aquí: si no estás de acuerdo dime qué quieres que hagamos y te mando nuevo presupuesto. Gracias: {$presupuestoUrl}";

        $indexContext = $this->buildIndexContextFromRequest($request);

        return view('presupuestos.factura', compact('presupuesto', 'items', 'iva', 'mensaje', 'indexContext'));
    }



    public function descargarPDF($presupuestoId)
    {
        try {
            $presupuesto = DB::table('appointments')
                ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
                ->join('users', 'bikes.user_id', '=', 'users.id')
                ->where('appointments.id', $presupuestoId)
                ->select('appointments.*', 'bikes.nombre as bicicleta_nombre', 'bikes.marca as marca', 'users.name as usuario_nombre')
                ->first();

            if (!$presupuesto) {
                abort(404, 'Presupuesto no encontrado');
            }

            $items = DB::table('appointment_component')
                ->join('components', 'appointment_component.componente_id', '=', 'components.id')
                ->where('appointment_component.appointment_id', $presupuestoId)
                ->select('appointment_component.*', 'components.nombre as componente_nombre')
                ->get();

            $limpiarNombre  = fn($texto) => preg_replace('/[^A-Za-z0-9_\-]/', '_', $texto);
            $usuarioLimpio  = $limpiarNombre($presupuesto->usuario_nombre);
            $bicicletaLimpia = $limpiarNombre($presupuesto->bicicleta_nombre);
            $fecha          = date('Y-m-d', strtotime($presupuesto->created_at));
            $nombreArchivo  = "Presupuesto_{$usuarioLimpio}_{$bicicletaLimpia}_{$fecha}.pdf";

            $pdf = Pdf::loadView('pdf.presupuesto', compact('presupuesto', 'items'));

            DB::table('appointments')
                ->where('id', $presupuestoId)
                ->update(['presupuesto_enviado' => true]);

            return $pdf->download($nombreArchivo);
        } catch (\Exception $e) {
            Log::error('[PresupuestoController] Error en descargarPDF', [
                'presupuesto_id' => $presupuestoId,
                'error'          => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Error al generar el PDF.');
        }
    }

    public function edit($id)
    {
        $hasPrecioMaterial = Schema::hasColumn('appointment_component', 'precio_material');

        // Obtener el presupuesto con los datos de la bicicleta y el usuario
        $presupuesto = DB::table('appointments')
            ->leftJoin('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->leftJoin('users', 'bikes.user_id', '=', 'users.id')
            ->select('appointments.*', 'bikes.id as bike_id', 'bikes.nombre as bike_nombre', 'users.name as user_nombre')
            ->where('appointments.id', $id)
            ->first();

        if (!$presupuesto) {
            abort(404);
        }

        // Obtener todos los ítems asociados a este presupuesto
        $presupuestoItemsQuery = DB::table('appointment_component')
            ->join('components', 'appointment_component.componente_id', '=', 'components.id')
            ->where('appointment_component.appointment_id', $id)
            ->select([
                'appointment_component.id',
                'appointment_component.appointment_id',
                'appointment_component.componente_id',
                'appointment_component.texto',
                'appointment_component.total_precio', // Ahora obtenemos el precio del presupuesto_item
                'appointment_component.horas_trabajo', // Ahora obtenemos las horas de trabajo editadas
                'components.nombre as componente_nombre',
                'appointment_component.descuento',
            ]);

        if ($hasPrecioMaterial) {
            $presupuestoItemsQuery->addSelect('appointment_component.precio_material');
        } else {
            $presupuestoItemsQuery->selectRaw('0 as precio_material');
        }

        $presupuesto_items = $presupuestoItemsQuery->get();

        $usuariosTaller = DB::table('users')
            ->whereIn('role', ['admin', 'taller'])
            ->select('id', 'name')
            ->get();





        // Obtener todas las bicicletas y componentes disponibles
        $bikes = DB::table('bikes')->get();
        $components = DB::table('components')->get();

        return view('presupuestos.edit', compact('presupuesto', 'bikes', 'components', 'presupuesto_items', 'usuariosTaller'));
    }



    public function update(Request $request, $id)
    {
        $hasPrecioMaterial = Schema::hasColumn('appointment_component', 'precio_material');

        $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componentes' => 'nullable|array',
            'horas_trabajo' => 'nullable|array',
            'precio' => 'nullable|array',
            'precio_material' => 'nullable|array',
            'textos' => 'nullable|array',
            'descuento' => 'nullable|array',
            'asignacion_taller' => 'nullable|array',
            'asignacion_taller.*' => 'exists:users,id',
            'prioridad' => 'nullable|string',
            'idprograma' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Siempre actualizar campos generales
            DB::table('appointments')
                ->where('id', $id)
                ->update([
                    'bike_id' => $request->bike_id,
                    'prioridad' => $request->prioridad,
                    'asignacion_taller' => json_encode($request->asignacion_taller ?? []),
                    'updated_at' => now(),
                ]);

            // Si hay componentes, procesarlos
            if (!empty($request->componentes)) {
                $componentesActuales = DB::table('appointment_component')
                    ->where('appointment_id', $id)
                    ->pluck('id', 'componente_id')
                    ->toArray();

                $totalPresupuesto = 0;
                $totalDescuento = 0;
                $totalHoras = 0;

                foreach ($request->componentes as $index => $componente_id) {
                    $horas_trabajo = (int) $request->horas_trabajo[$index];
                    $precio_mano_obra = (float) $request->precio[$index];
                    $precio_material = (float) ($request->precio_material[$index] ?? 0);
                    $total_descuento = (float) $request->descuento[$index];
                    $precio_bruto = $precio_mano_obra + $precio_material;

                    $totalPresupuesto += max($precio_bruto - $total_descuento, 0);
                    $totalDescuento += $total_descuento;
                    $totalHoras += $horas_trabajo;

                    $datosItem = [
                        'appointment_id' => $id,
                        'horas_trabajo' => $horas_trabajo,
                        'total_precio' => $precio_mano_obra,
                        'descuento' => $total_descuento,
                        'texto' => $request->textos[$index] ?? '',
                        'updated_at' => now(),
                    ];

                    if ($hasPrecioMaterial) {
                        $datosItem['precio_material'] = $precio_material;
                    }

                    if (isset($componentesActuales[$componente_id])) {
                        DB::table('appointment_component')
                            ->where('id', $componentesActuales[$componente_id])
                            ->update($datosItem);
                        unset($componentesActuales[$componente_id]);
                    } else {
                        $datosItem['componente_id'] = $componente_id;
                        $datosItem['created_at'] = now();
                        DB::table('appointment_component')->insert($datosItem);
                    }
                }

                // Eliminar componentes que ya no están
                if (!empty($componentesActuales)) {
                    DB::table('appointment_component')
                        ->whereIn('id', $componentesActuales)
                        ->delete();
                }

                // Actualizar totales
                DB::table('appointments')
                    ->where('id', $id)
                    ->update([
                        'horas_total' => $totalHoras,
                        'tiempo_reparacion' => $totalHoras,
                        'precio_total' => $totalPresupuesto,
                        'descuento' => $totalDescuento,
                        'idprograma' => $request->idprograma,
                        'calendario' => $request->calendario
                    ]);
            }

            DB::commit();

            return redirect()->route('presupuestos.index', $this->buildIndexContextFromRequest($request))
                ->with('success', 'Presupuesto actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PresupuestoController] Error en update', [
                'presupuesto_id' => $id,
                'error'          => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Error al actualizar el presupuesto: ' . $e->getMessage());
        }
    }


    public function actualizarEstado(Request $request, $id)
    {
        $resultado = $this->cita($request, $id);

        return redirect()->route('presupuestos.index', $this->buildIndexContextFromRequest($request))
            ->with($resultado['tipo'], $resultado['mensaje']);
    }

    public function cita($request, $id)
    {
        $request->validate([
            'estado' => 'required|in:aprobado,denegado',
        ]);

        // Obtener la cita existente
        $cita = DB::table('appointments')->where('id', $id)->first();

        if (!$cita) {
            return [
                'tipo' => 'error',
                'mensaje' => 'Cita no encontrada.'
            ];
        }

        if ($request->estado === 'aprobado') {
            DB::beginTransaction(); // Iniciar transacción

            try {
                // Actualizar el estado de la cita a 'pendiente' si está aprobada
                DB::table('appointments')->where('id', $id)->update([
                    'estado' => 'pendiente',
                    'updated_at' => now(),
                ]);

                DB::commit(); // Confirmar transacción

                return [
                    'tipo' => 'success',
                    'mensaje' => 'Cita actualizada a pendiente.'
                ];
            } catch (\Exception $e) {
                DB::rollBack(); // Revertir cambios en caso de error
                return [
                    'tipo' => 'error',
                    'mensaje' => 'Error al actualizar la cita.'
                ];
            }
        } elseif ($request->estado === 'denegado') {
            try {
                DB::table('appointments')->where('id', $id)->update([
                    'estado'     => 'denegado',
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('[PresupuestoController] Error en cita (denegado)', [
                    'presupuesto_id' => $id,
                    'error'          => $e->getMessage(),
                ]);
                return ['tipo' => 'error', 'mensaje' => 'Error al denegar el presupuesto.'];
            }

            return [
                'tipo'    => 'success',
                'mensaje' => 'Presupuesto denegado.',
            ];
        }
    }



















    public function confirmarPresupuesto(Request $request, $presupuestoId)
    {
        $token = $request->query('token');
        // Obtener presupuesto y verificar token
        $presupuesto = DB::table('appointments')
            ->join('bikes', 'appointments.bike_id', '=', 'bikes.id')
            ->join('users', 'bikes.user_id', '=', 'users.id')
            ->where('appointments.token_presupuesto', $token)
            ->where('appointments.id', $presupuestoId)
            ->select('appointments.*', 'bikes.nombre as bicicleta_nombre', 'users.name as cliente_nombre') // Cambié usuario_nombre a cliente_nombre
            ->first();

        if (!$presupuesto) {
            return response()->view('presupuestos.error', ['mensaje' => 'Token inválido o presupuesto no encontrado'], 403);
        }
        // Verificar si el presupuesto ya fue aprobado o rechazado
        if ($presupuesto->estado === 'aprobado') {
            return response()->view('presupuestos.error', ['mensaje' => 'Este presupuesto ya ha sido aprobado.'], 403);
        }

        if ($presupuesto->estado === 'denegado') {
            return response()->view('presupuestos.error', ['mensaje' => 'Este presupuesto ha sido rechazado.'], 403);
        }



        return view('presupuestos.confirmacion', compact('presupuesto'));
    }


    public function procesarConfirmacion(Request $request, $presupuestoId)
    {
        $token = $request->query('token');
        $estado = $request->input('accion'); // 'aprobado' o 'denegado'

        // Verificar que el estado es válido
        if (!in_array($estado, ['aprobado', 'denegado'])) {
            return response()->view('presupuestos.error', ['mensaje' => '❌ Estado inválido.'], 400);
        }

        // Obtener presupuesto y verificar token
        $presupuesto = DB::table('appointments')
            ->where('id', $presupuestoId)
            ->where('token_presupuesto', $token)
            ->first();

        if (!$presupuesto) {
            return response()->view('presupuestos.error', ['mensaje' => '❌ Token inválido o presupuesto no encontrado'], 403);
        }

        // Evitar doble aprobación
        if ($presupuesto->estado === 'aprobado') {
            return view('presupuestos.aprobada', ['mensaje' => '⚠️ Este presupuesto ya ha sido aprobado anteriormente.']);
        }
        $request = new Request(['estado' => $estado]);
        // Llamar a la función cita() para procesar la aprobación o denegación
        $resultado = $this->cita($request, $presupuestoId);

        // Mostrar la vista correspondiente con el mensaje de la función cita()
        if ($estado === 'aprobado') {
            return view('presupuestos.aprobada', ['mensaje' => $resultado['mensaje']]);
        } else {
            return view('presupuestos.denegada', ['mensaje' => '❌ Has rechazado el presupuesto. Si cambias de opinión, contáctanos.']);
        }
    }

    public function destroy(Request $request, $id)
    {
        // Iniciar una transacción para asegurarnos de que ambas tablas se actualicen correctamente
        DB::beginTransaction();

        try {
            // Eliminar los componentes asociados a la cita
            DB::table('appointment_component')->where('appointment_id', $id)->delete();

            // Eliminar la cita
            DB::table('appointments')->where('id', $id)->delete();

            // Confirmar la transacción
            DB::commit();

            // Redirigir con éxito
            return redirect()->route('presupuestos.index', $this->buildIndexContextFromRequest($request))
                ->with('success', 'Cita y componentes asociados eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('[PresupuestoController] Error en destroy', [
                'presupuesto_id' => $id,
                'error'          => $e->getMessage(),
            ]);
            return redirect()->route('presupuestos.index', $this->buildIndexContextFromRequest($request))
                ->with('error', 'Ocurrió un error al eliminar la cita: ' . $e->getMessage());
        }
    }
}
