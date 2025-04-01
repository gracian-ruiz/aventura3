<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtiene el valor del buscador
    
        // Filtra por nombre si se ingresó un término de búsqueda
        $components = Component::when($search, function ($query) use ($search) {
            return $query->where('nombre', 'LIKE', "%{$search}%");
        })->paginate(10); // Puedes cambiar el número de registros por página

        return view('components.index', compact('components', 'search'));
    }

    public function create()
    {
        return view('components.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_preaviso' => 'nullable|integer|min:0',
            'fecha_revision' => 'required|integer|min:1',
            'hora_taller' => 'required|integer|min:0', // Asegurar que se recibe
            'precio' => 'required|numeric|min:0', // Asegurar que se recibe y es un número válido
        ]);
    
        Component::create($validated);
    
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
            'fecha_preaviso' => 'required|integer|min:0',
            'fecha_revision' => 'required|integer|min:1',
            'hora_taller' => 'required', // Nueva validación
            'precio' => 'required', // Nueva validación
        ]);
    
        $component->update($validated);
    
        return redirect()->route('components.index')->with('success', '✅ Componente actualizado correctamente.');
    }
    

    public function destroy(Component $component)
    {
        $component->delete();
        return redirect()->route('components.index')->with('success', '🗑️ Componente eliminado.');
    }
}
