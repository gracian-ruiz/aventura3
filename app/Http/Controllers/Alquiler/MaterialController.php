<?php

namespace App\Http\Controllers\Alquiler;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Material::query();
    
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%");
        }
    
        $materials = $query->orderBy('id', 'desc')->paginate(10);
    
        return view('alquiler.material.index', compact('materials'));
    }
    
    public function create()
    {
        return view('alquiler.material.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|string',
            'nombre' => 'required|string',
            'talla' => 'nullable|string',
            'estado' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_dia' => 'required|numeric',
        ]);
    
        Material::create($validated);
    
        return redirect()->route('material.index')->with('success', 'Material creado correctamente.');
    }
    

    public function edit(Material $material)
    {
        return view('alquiler.material.edit', compact('material'));
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'tipo' => 'required|string',
            'nombre' => 'required|string',
            'talla' => 'nullable|string',
            'estado' => 'required|string',
            'descripcion' => 'nullable|string',
            'precio_dia' => 'required|numeric',
        ]);
    
        $material->update($validated);
    
        return redirect()->route('material.index')->with('success', 'Material actualizado.');
    }
    

    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('material.index')->with('success', 'Material eliminado.');
    }
}
