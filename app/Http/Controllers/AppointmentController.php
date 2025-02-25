<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bike;
use Illuminate\Http\Request;
use App\Models\Component;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        $appointments = Appointment::with('bike.user', 'componente')
            ->where('estado', 'pendiente')
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                ->orWhereHas('bike.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('componente', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN prioridad = 'urgente' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'asc')
            ->paginate(8);
    
        return view('appointments.index', compact('appointments', 'search'));
    }
    
    
    
    public function create()
    {
        $bikes = Bike::all();
        $componentes = Component::all(); // Agregamos los componentes
    
        return view('appointments.create', compact('bikes', 'componentes'));
    }
    
    
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'tiempo_estimado' => 'nullable|string'
        ]);
    
        Appointment::create([
            'bike_id' => $request->bike_id,
            'componente_id' => $request->componente_id,
            'prioridad' => $request->prioridad,
            'tiempo_estimado' => $request->tiempo_estimado,
            'estado' => 'pendiente'
        ]);
    
        return redirect()->route('appointments.index')->with('success', 'Cita registrada correctamente.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'bike_id' => 'required|exists:bikes,id',
            'componente_id' => 'nullable|exists:components,id',
            'prioridad' => 'required|in:normal,urgente',
            'tiempo_estimado' => 'nullable|string',
        ]);

        $appointment->update($validated);

        return redirect()->route('appointments.index')->with('success', '✅ Cita actualizada correctamente.');
    }

    public function edit(Appointment $appointment)
    {
        $bikes = Bike::all();
        $componentes = Component::all();
        return view('appointments.edit', compact('appointment', 'bikes', 'componentes'));
    }

    

    public function updateEstado(Appointment $appointment)
    {
        // Marcar cita como completada y registrar la fecha de actualización
        $appointment->update([
            'estado' => 'completada',
            'updated_at' => now()
        ]);

        // Redirigir a la creación de la revisión
        return redirect()->route('bikes.revisions.create', $appointment->bike_id)
                        ->with('success', 'Cita convertida en revisión.');
    }

    public function historico(Request $request)
    {
        $search = $request->input('search');
    
        $completedAppointments = Appointment::with('bike.user', 'componente')
            ->where('estado', 'completada')
            ->when($search, function ($query, $search) {
                $query->whereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })
                ->orWhereHas('bike.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('componente', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                });
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10);
    
        return view('appointments.historico', compact('completedAppointments', 'search'));
    }
    
    

    public function destroy(Appointment $appointment)
    {
        if ($appointment->estado === 'completada') {
            $appointment->delete();
            return redirect()->route('appointments.historico')->with('success', '✅ Cita eliminada del historial.');
        }
    
        return redirect()->route('appointments.index')->with('error', 'No puedes eliminar una cita pendiente.');
    }
    
    public function historic()
    {
        $completedAppointments = Appointment::where('estado', 'completada')
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('appointments.historic', compact('completedAppointments'));
    }


}
