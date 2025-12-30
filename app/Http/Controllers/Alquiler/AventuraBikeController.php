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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AventuraBikeController extends Controller
{
    public function bicismontaña()
    {

        return view('alquiler.aventurabike.montana');
    }
        public function bicismontañados()
    {

        return view('alquiler.aventurabike.montana2');
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
            'acepta_condiciones' => 'accepted',
            'observaciones' => 'nullable|string|max:1000',
            // 📸 Validación de imágenes del DNI
            'imagenes_dni' => 'required|array|min:1',
            'imagenes_dni.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            // 🧾 Mensajes personalizados
            'bicicletas.required' => 'Debes añadir al menos una bicicleta para la reserva.',
            'bicicletas.*.fecha_inicio.required' => 'Debes indicar la fecha de inicio del alquiler.',
            'bicicletas.*.fecha_fin.required' => 'Debes indicar la fecha de fin del alquiler.',
            'bicicletas.*.fecha_fin.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
            'bicicletas.*.talla.required' => 'Por favor, selecciona la talla de la bicicleta.',
            'bicicletas.*.tipo.required' => 'Por favor, selecciona el tipo de bicicleta.',
            'bicicletas.*.cantidad.required' => 'Indica cuántas bicicletas deseas alquilar.',
            'bicicletas.*.cantidad.min' => 'Debe alquilar al menos una bicicleta.',
            'acepta_condiciones.accepted' => 'Debes aceptar las condiciones generales del alquiler para continuar.',
            // 📸 Mensajes para imágenes del DNI
            'imagenes_dni.required' => 'Debes subir al menos una imagen del DNI.',
            'imagenes_dni.min' => 'Debes subir al menos una imagen del DNI.',
            'imagenes_dni.*.required' => 'La imagen del DNI es obligatoria.',
            'imagenes_dni.*.image' => 'El archivo debe ser una imagen válida.',
            'imagenes_dni.*.mimes' => 'Las imágenes del DNI deben ser en formato: JPEG, JPG, PNG o WEBP.',
            'imagenes_dni.*.max' => 'Cada imagen del DNI no puede superar los 5MB.',
        ]);

        // 🕵️‍♂️ Honeypot anti-spam
        if (!empty($request->input('website'))) {
            return back()->with('error', 'Detección de spam. Solicitud rechazada.');
        }

        DB::beginTransaction();

        try {
            // 👤 Crear o buscar usuario
            $usuario = UsuarioAlquiler::firstOrCreate(
                ['dni' => $request->dni],
                [
                    'nombre' => $request->nombre . ' ' . $request->apellido,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                ]
            );

            // 📅 Fechas globales
            $fechasInicio = collect($request->bicicletas)->pluck('fecha_inicio');
            $fechasFin = collect($request->bicicletas)->pluck('fecha_fin');
            $fechaInicioGlobal = $fechasInicio->min();
            $fechaFinGlobal = $fechasFin->max();

            // 🚲 Crear alquiler
            $alquiler = Alquiler::create([
                'usuario_id' => $usuario->id,
                'fecha_inicio' => $fechaInicioGlobal,
                'fecha_fin' => $fechaFinGlobal,
                'estado' => 'reservado',
                'total_precio' => 0,
                'reserva_precio' => 0,
                'descuento' => 0,
                'web' => true,
                'observaciones' => $request->input('observaciones'),
                'notificacion' => true,
            ]);

            $totalPrecio = 0;
            $reservaTotal = 0;
            $fallo = false;
            $incidencias = [];

            foreach ($request->bicicletas as $bicicleta) {
                $tipo = $bicicleta['tipo'];
                $talla = $bicicleta['talla'];
                $cantidad = $bicicleta['cantidad'];
                $fechaInicio = $bicicleta['fecha_inicio'];
                $fechaFin = $bicicleta['fecha_fin'];

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

            // 💾 Actualizar totales
            $alquiler->update([
                'total_precio' => $totalPrecio,
                'reserva_precio' => $reservaTotal,
                'fallo' => $fallo,
                'incidencia' => $fallo ? implode(' | ', $incidencias) : null,
            ]);

            // 📸 Guardar imágenes del DNI en zona PRIVADA
            if ($request->hasFile('imagenes_dni')) {
                foreach ($request->file('imagenes_dni') as $index => $file) {
                    $nombreArchivo = time() . "_dni_" . $index . '.' . $file->getClientOriginalExtension();
                    $ruta = $file->storeAs('private/dnis', $nombreArchivo); // 👈 sin 'public'

                    DB::table('usuario_alquiler_fotos')->insert([
                        'alquiler_id' => $alquiler->id,
                        'ruta' => $ruta,
                        'tipo' => 'dni',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ✉️ Enviar correo solo si no estás en local
            try {
                if (app()->environment('production')) {
                    Mail::to($usuario->email)
                        ->send(new \App\Mail\ReservaAlquilerMail(
                            $alquiler,
                            $usuario,
                            $request->bicicletas,
                            $request->input('observaciones')
                        ));
                    Log::info('Correo de reserva enviado correctamente a: ' . $usuario->email);
                } else {
                    Log::info('📬 Correo NO enviado (modo local)');
                }
            } catch (\Exception $mailError) {
                Log::error('Error al enviar correo de reserva: ' . $mailError->getMessage());
            }

            DB::commit();
            return back()->with('success', '✅ ¡Reserva enviada correctamente! Pronto recibirás un correo de confirmación.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Error en reserva: ' . $e->getMessage());
            return back()->with('error', '⚠️ Ha ocurrido un error al procesar tu reserva. Inténtalo de nuevo.');
        }
    }
    public function mostrarDniPrivado($id)
    {
        $foto = DB::table('usuario_alquiler_fotos')->where('id', $id)->first();

        if (!$foto || !Storage::exists($foto->ruta)) {
            abort(404, 'Imagen no encontrada');
        }

        // 📂 Muestra la imagen de forma segura sin hacerla pública
        return response()->file(storage_path('app/' . $foto->ruta));
    }
    
}
