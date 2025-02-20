<?php

namespace App\Http\Controllers;

use App\Models\Component;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    public function index()
    {
        $components = Component::paginate(10);
        return view('components.index', compact('components'));
    }

    public function create()
    {
        return view('components.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_preaviso' => 'nullable|integer',
        ]);

        Component::create($request->all());

        return redirect()->route('components.index')->with('success', '✅ Componente creado correctamente.');
    }

    public function edit(Component $component)
    {
        return view('components.edit', compact('component'));
    }

    public function update(Request $request, Component $component)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_preaviso' => 'nullable|integer',
        ]);

        $component->update($request->all());

        return redirect()->route('components.index')->with('success', '✅ Componente actualizado correctamente.');
    }

    public function destroy(Component $component)
    {
        $component->delete();
        return redirect()->route('components.index')->with('success', '🗑️ Componente eliminado.');
    }
}
