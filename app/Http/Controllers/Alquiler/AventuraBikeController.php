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

        if (!empty($request->input('website'))) {
            return back()->with('error', 'Detección de spam. Solicitud rechazada.');
        }
        
    
        DB::beginTransaction();
    
        try {
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
                'oberservaciones'=> $request->input('observaciones'),
                'notificacion' => true

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
            Mail::to($usuario->email)
            ->send(new \App\Mail\ReservaAlquilerMail($alquiler, $usuario, $request->bicicletas, $request->input('observaciones')
                       ));
        
        

    
            DB::commit();
    
            return back()->with('success', 'Formulario enviado correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el alquiler: ' . $e->getMessage()])->withInput();
        }
    }
    
}
