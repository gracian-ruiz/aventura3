<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        if (!in_array((int) $perPage, [10, 50, 100, 200], true)) {
            $perPage = 10;
        }

        $components = Component::when($search, function ($query) use ($search) {
            return $query->where('nombre', 'LIKE', "%{$search}%");
        })->paginate((int) $perPage)->appends(['search' => $search, 'per_page' => $perPage]);

        return view('components.index', compact('components', 'search', 'perPage'));
    }

    public function create()
    {
        return view('components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'fecha_preaviso' => 'nullable|integer|min:0',
            'fecha_revision' => 'required|integer|min:1',
            'hora_taller' => 'required|integer|min:0', // Asegurar que se recibe
            'precio' => 'required|numeric|min:0', // Asegurar que se recibe y es un número válido
            'orden' => 'nullable|integer|min:0', // Validar el campo 'orden' (puede ser nulo)
            'enviar_mensaje' => 'nullable|boolean',
        ]);

        $validated['enviar_mensaje'] = $request->boolean('enviar_mensaje');
    
        try {
            Component::create($validated);
        } catch (\Exception $e) {
            Log::error('Error al crear componente', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al guardar el componente.'])->withInput();
        }

        return redirect()->route('components.index')->with('success', '✅ Componente creado correctamente.');
    }

    public function edit(Component $component)
    {
        return view('components.edit', compact('component'));
    }

    public function update(Request $request, Component $component)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'fecha_preaviso' => 'required|integer|min:0',
            'fecha_revision' => 'required|integer|min:1',
            'hora_taller' => 'required|integer|min:0', // Asegurar que se recibe
            'precio' => 'required|numeric|min:0', // Asegurar que se recibe
            'orden' => 'nullable|integer|min:0', // Validar el campo 'orden' (puede ser nulo)
            'enviar_mensaje' => 'nullable|boolean',
        ]);

        $validated['enviar_mensaje'] = $request->boolean('enviar_mensaje');
    
        try {
            $component->update($validated);
        } catch (\Exception $e) {
            Log::error('Error al actualizar componente', ['component_id' => $component->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al actualizar el componente.'])->withInput();
        }

        return redirect()->route('components.index')->with('success', '✅ Componente actualizado correctamente.');
    }

    public function destroy(Component $component)
    {
        try {
            $component->delete();
        } catch (\Exception $e) {
            Log::error('Error al eliminar componente', ['component_id' => $component->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al eliminar el componente.']);
        }

        return redirect()->route('components.index')->with('success', '🗑️ Componente eliminado.');
    }
}
