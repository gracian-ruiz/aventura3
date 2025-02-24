<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Illuminate\Http\Request;

class RevisionController extends Controller
{
    /**
     * ✅ Mostrar todas las revisiones en general
     */
    public function allRevisions(Request $request)
    {
        $search = $request->input('search');

        $revisions = Revision::with('bike', 'componente')
            ->when($search, function ($query) use ($search) {
                return $query->where('descripcion', 'LIKE', "%{$search}%");
            })
            ->paginate(10);

        return view('revisions.index', compact('revisions', 'search'));
    }

    /**
     * ✅ Mostrar todas las revisiones de una bicicleta en particular con búsqueda
     */
    public function index(Request $request, Bike $bike)
    {
        $search = $request->input('search');

        $revisions = $bike->revisions()
            ->with('componente')
            ->when($search, function ($query) use ($search) {
                return $query->where('descripcion', 'LIKE', "%{$search}%");
            })
            ->paginate(10);

        return view('revisions.index', compact('bike', 'revisions', 'search'));
    }

    /**
     * ✅ Formulario para añadir una revisión a una bicicleta
     */
    public function create(Bike $bike)
    {
        $componentes = Component::all();
        return view('revisions.create', compact('bike', 'componentes'));
    }

    /**
     * ✅ Guardar una nueva revisión de una bicicleta
     */
    public function store(Request $request, Bike $bike)
    {
        $validated = $request->validate([
            'componente_id' => 'required|exists:components,id',
            'fecha_revision' => 'required|date',
            'descripcion' => 'required|string',
            'proxima_revision' => 'nullable|date',
        ]);

        $bike->revisions()->create($validated);

        return redirect()->route('bikes.revisions.index', $bike->id)
            ->with('success', '✅ Revisión añadida correctamente.');
    }

    /**
     * ✅ Mostrar formulario de edición de una revisión
     */
    public function edit(Bike $bike, Revision $revision)
    {
        // 🔹 Obtener todos los componentes disponibles para el desplegable
        $componentes = Component::all();
    
        return view('revisions.edit', compact('bike', 'revision', 'componentes'));
    }
    

    /**
     * ✅ Actualizar revisión
     */
    public function update(Request $request, Bike $bike, Revision $revision)
    {
        $request->validate([
            'componente_id' => 'required|exists:components,id',
            'fecha_revision' => 'required|date',
            'descripcion' => 'required|string',
            'proxima_revision' => 'nullable|date',
        ]);

        $revision->update($request->all());

        return redirect()->route('bikes.revisions.index', $bike->id)
            ->with('success', '✅ Revisión actualizada correctamente.');
    }

    /**
     * ✅ Eliminar una revisión
     */
    public function destroy(Bike $bike, Revision $revision)
    {
        $revision->delete();

        return redirect()->route('bikes.revisions.index', $bike->id)
            ->with('success', '🗑️ Revisión eliminada.');
    }
}
