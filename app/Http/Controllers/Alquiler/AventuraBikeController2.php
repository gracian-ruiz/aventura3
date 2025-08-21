<?php

namespace App\Http\Controllers\Alquiler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UsuarioAlquiler;
use App\Models\BicicletaAlquiler;
use App\Models\Alquiler;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservaAlquilerMail;
use Ssheduardo\Redsys\Facades\Redsys;
use Sermepa\Tpv\Tpv;
use DateTime;
use Illuminate\Support\Facades\Log;







class AventuraBikeController extends Controller
{
    public function bicismontaña()
    {

        return view('alquiler.aventurabike.montana');
    }


    public function comprobarDisponibilidad(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string',
            'talla' => 'required|string',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
        ]);

        $tipo = $request->tipo;
        $talla = $request->talla;
        $fechaInicio = $request->fechaInicio;
        $fechaFin = $request->fechaFin;

        // Materiales ocupados en esas fechas, por tipo y talla
        $ocupadas = DB::table('alquiler_material')
            ->join('alquileres', 'alquiler_material.alquiler_id', '=', 'alquileres.id')
            ->join('materials', 'alquiler_material.material_id', '=', 'materials.id')
            ->where('materials.tipo', $tipo)
            ->where('materials.talla', $talla)
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('alquiler_material.fecha_inicio', [$fechaInicio, $fechaFin])
                    ->orWhereBetween('alquiler_material.fecha_fin', [$fechaInicio, $fechaFin])
                    ->orWhere(function ($query) use ($fechaInicio, $fechaFin) {
                        $query->where('alquiler_material.fecha_inicio', '<=', $fechaInicio)
                            ->where('alquiler_material.fecha_fin', '>=', $fechaFin);
                    });
            })
            ->where('alquileres.estado', '!=', 'finalizado')
            ->count();

        // Total de materiales de ese tipo y talla disponibles en stock
        $totalStock = DB::table('materials')
            ->where('tipo', $tipo)
            ->where('talla', $talla)
            ->count();

        $disponible = $totalStock > $ocupadas;

        return response()->json([
            'disponible' => $disponible,
            'materiales_ocupados' => $ocupadas,
            'total_stock' => $totalStock,
        ]);
    }







    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'apellido' => 'required|string',
            'email' => 'required|email',
            'telefono' => 'required|string',
            'dni' => 'required|string',
            'direccion' => 'nullable|string',
            'bicicletas' => 'required|array|min:1',
            'bicicletas.*.fecha_inicio' => 'required|date',
            'bicicletas.*.fecha_fin' => 'required|date|after_or_equal:bicicletas.*.fecha_inicio',
            'bicicletas.*.talla' => 'required|string',
            'bicicletas.*.tipo' => 'required|string',
            'bicicletas.*.cantidad' => 'required|integer|min:1',
            'observaciones' => 'nullable|string|max:1000',

        ]);


        // Ahora $total tiene el coste total de todas las bicicletas reservadas


        if (!empty($request->input('website'))) {
            return back()->with('error', 'Detección de spam. Solicitud rechazada.');
        }

        DB::beginTransaction();

        try {

            //calcular el order
            $lastId = DB::table('alquileres')->max('id') ?? 0;
            $timestamp = now()->format('YmdHis'); // AñoMesDíaHoraMinutoSegundo (ej: 20250616173542)
            $order = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT) . $timestamp;
            $order = substr(date('YmdHis'), -12);


            // 1️⃣ Buscar o crear el usuario
            $usuario = UsuarioAlquiler::firstOrCreate(
                ['dni' => $request->dni],
                [
                    'nombre' => $request->nombre . ' ' . $request->apellido,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]
            );

            // 2️⃣ Obtener fechas globales
            $fechasInicio = collect($request->bicicletas)->pluck('fecha_inicio');
            $fechasFin = collect($request->bicicletas)->pluck('fecha_fin');
            $fechaInicioGlobal = $fechasInicio->min();
            $fechaFinGlobal = $fechasFin->max();

            // 3️⃣ Crear alquiler con web=true
            $alquiler = Alquiler::create([
                'usuario_id' => $usuario->id,
                'fecha_inicio' => $fechaInicioGlobal,
                'fecha_fin' => $fechaFinGlobal,
                'estado' => 'reservado',
                'total_precio' => 0,
                'reserva_precio' => 0,
                'descuento' => 0,
                'web' => true, // <- importante
                'oberservaciones' => $request->input('observaciones'),
                'notificacion' => true,
                'order' => $order,
                'pago' => 'pago'

            ]);

            $totalPrecio = 0;
            $reservaTotal = 0;
            $incidencias = [];
            $fallo = false;

            foreach ($request->bicicletas as $bicicleta) {
                $tipo = $bicicleta['tipo'];
                $talla = $bicicleta['talla'];
                $cantidad = $bicicleta['cantidad'];
                $fechaInicio = $bicicleta['fecha_inicio'];
                $fechaFin = $bicicleta['fecha_fin'];

                // Buscar materiales disponibles
                $materialesDisponibles = Material::where('tipo', $tipo)
                    ->where('talla', $talla)
                    ->whereNotIn('id', function ($query) use ($fechaInicio, $fechaFin) {
                        $query->select('material_id')
                            ->from('alquiler_material')
                            ->join('alquileres', 'alquiler_material.alquiler_id', '=', 'alquileres.id')
                            ->where(function ($q) use ($fechaInicio, $fechaFin) {
                                $q->whereBetween('alquiler_material.fecha_inicio', [$fechaInicio, $fechaFin])
                                    ->orWhereBetween('alquiler_material.fecha_fin', [$fechaInicio, $fechaFin])
                                    ->orWhere(function ($q2) use ($fechaInicio, $fechaFin) {
                                        $q2->where('alquiler_material.fecha_inicio', '<=', $fechaInicio)
                                            ->where('alquiler_material.fecha_fin', '>=', $fechaFin);
                                    });
                            })
                            ->where('alquileres.estado', '!=', 'finalizado');
                    })
                    ->take($cantidad)
                    ->get();

                if ($materialesDisponibles->count() < $cantidad) {
                    $fallo = true;
                    $disponibles = $materialesDisponibles->count();

                    $fechaInicioFormatted = Carbon::parse($fechaInicio)->format('d/m/Y');
                    $fechaFinFormatted = Carbon::parse($fechaFin)->format('d/m/Y');

                    $incidencias[] = "$tipo talla $talla ($fechaInicioFormatted a $fechaFinFormatted): solicitados $cantidad, disponibles $disponibles.\nNombre: {$usuario->name}\nTeléfono: {$usuario->telefono}\nCorreo: {$usuario->email}";



                    continue; // no intentamos insertar, solo registramos la incidencia
                }


                foreach ($materialesDisponibles as $m) {
                    $dias = Carbon::parse($fechaInicio)->diffInDays(Carbon::parse($fechaFin)) + 1;
                    $precioUnitario = $m->precio_dia * $dias;
                    $reservaPrecio = $m->reserva_precio * $dias;

                    $alquiler->materiales()->attach($m->id, [
                        'fecha_inicio' => $fechaInicio,
                        'fecha_fin' => $fechaFin,
                        'precio_unitario' => $m->precio_dia,
                        'descuento' => 0,
                        'reserva_precio' => $reservaPrecio,
                        'subtotal' => $precioUnitario,
                    ]);

                    $totalPrecio += $precioUnitario;
                    $reservaTotal += $reservaPrecio;
                }
            }

            // 4️⃣ Guardar totales e incidencias si hubo
            $alquiler->update([
                'total_precio' => $totalPrecio,
                'reserva_precio' => $reservaTotal,
                'fallo' => $fallo,
                'incidencia' => $fallo ? implode(' | ', $incidencias) : null,
            ]);

            // Enviar correo al usuario y a un email fijo
            /*             Mail::to($usuario->email)
            ->send(new \App\Mail\ReservaAlquilerMail($alquiler, $usuario, $request->bicicletas, $request->input('observaciones')
                       )); */




            DB::commit();

            $total = 0;

            foreach ($request->input('bicicletas') as $bicicleta) {
                $fechaInicio = new DateTime($bicicleta['fecha_inicio']);
                $fechaFin = new DateTime($bicicleta['fecha_fin']);

                // Diferencia en días (inclusive)
                $interval = $fechaInicio->diff($fechaFin);
                $dias = $interval->days + 1; // +1 para incluir el día de inicio

                $cantidad = $bicicleta['cantidad'];

                $precioPorDia = 5;

                $subtotal = $dias * $precioPorDia * $cantidad;
                $total += $subtotal;
            }




            $tpv = new Tpv();

            $data = [
                'DS_MERCHANT_AMOUNT' => ($total * 100),
                'DS_MERCHANT_ORDER' => $order, // "000000000069"
                'DS_MERCHANT_MERCHANTCODE' => env('REDSYS_MERCHANT_CODE'),
                'DS_MERCHANT_CURRENCY' => env('REDSYS_CURRENCY'),
                'DS_MERCHANT_TRANSACTIONTYPE' => env('REDSYS_TRANSACTION_TYPE'),
                'DS_MERCHANT_TERMINAL' => env('REDSYS_TERMINAL'),
                'DS_MERCHANT_MERCHANTURL' => route('alquiler.response'),
                'DS_MERCHANT_URLOK' => route('alquiler.exito'),
                'DS_MERCHANT_URLKO' => route('alquiler.error'),
                'DS_MERCHANT_TITULAR' => "Reserva de bicicleta",
                'DS_MERCHANT_PRODUCTDESCRIPTION' => "Pago de reserva",
                'DS_MERCHANT_MERCHANTNAME' => 'Tu Comercio',
                'DS_MERCHANT_VERSION' => 'HMAC_SHA256_V1',
            ];



            // Codificamos a base64 el JSON
            $merchantParameters = base64_encode(json_encode($data));

            // Obtenemos la clave secreta desde .env
            $secretKey = env('REDSYS_SECRET_KEY');

            // Generamos la firma
            $signature = $tpv->generateMerchantSignature($merchantParameters, $order, $secretKey);

            // Mostramos la vista con los datos para enviar al TPV
            return view('alquiler.aventurabike.pagar', [
                'signatureVersion' => 'HMAC_SHA256_V1',
                'params' => $merchantParameters,
                'signature' => $signature,
                'urlTPV' => env('REDSYS_URL'),
            ]);


            /* return back()->with('success', 'Formulario enviado correctamente'); */
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el alquiler: ' . $e->getMessage()])->withInput();
        }
    }











    public function response(Request $request)
    {

        // 1. Capturar parámetros
        $merchantParametersBase64 = $request->input('Ds_MerchantParameters');
        $signature = $request->input('Ds_Signature');

        // 2. Decodificar y parsear los parámetros
        $json = base64_decode($merchantParametersBase64);
        $params = json_decode($json, true);

        // 3. Recuperar el Order
        $order = $params['Ds_Order'] ?? null;

        // 4. Recuperar el código de respuesta
        $codigoRespuesta = $params['Ds_Response'] ?? null;

        // 5. Validar firma (opcional pero recomendado)
        $tpv = new Tpv();
        /*     $firmaCorrecta = $tpv->verifySignature($merchantParametersBase64, $order, env('REDSYS_SECRET_KEY'), $signature);

    if (!$firmaCorrecta) {
        Log::error('Firma incorrecta en notificación TPV.');
        return response('ERROR', 400);
    } */

        // 6. Buscar y actualizar el alquiler
        $alquiler = Alquiler::where('order', $order)->first();

        if ($alquiler) {
            if ((int)$codigoRespuesta < 100) {
                $alquiler->pago = 'pagado';
            } else {
                $alquiler->pago = 'fallido';
            }
            $alquiler->save();
        }

        return response('OK', 200); // Obligatorio para Redsys
    }





    public function exito()
    {
        return back()->with('success', 'Formulario enviado correctamente');
    }

    public function error()
    {
        return back()->withErrors(['error' => 'Error al crear el alquiler: '])->withInput();
    }
}
