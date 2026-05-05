<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


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
            'tipo_fecha' => 'required|string|in:fija,opcional',
            'proxima_revision' => 'nullable|date',
        ]);
    
        if ($request->tipo_fecha === 'fija') {
            $componente = Component::findOrFail($request->componente_id);
            if ($componente->fecha_revision) {
                $validated['proxima_revision'] = Carbon::parse($request->fecha_revision)->addMonths($componente->fecha_revision);
            }
        }
    
        try {
            $bike->revisions()->create($validated);
        } catch (\Exception $e) {
            Log::error('Error al crear revisión', ['bike_id' => $bike->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al guardar la revisión.'])->withInput();
        }

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

        try {
            $revision->update($request->all());
        } catch (\Exception $e) {
            Log::error('Error al actualizar revisión', ['revision_id' => $revision->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al actualizar la revisión.'])->withInput();
        }

        return redirect()->route('bikes.revisions.index', $bike->id)
            ->with('success', '✅ Revisión actualizada correctamente.');
    }

    /**
     * ✅ Eliminar una revisión
     */
    public function destroy(Bike $bike, Revision $revision)
    {
        try {
            $revision->delete();
        } catch (\Exception $e) {
            Log::error('Error al eliminar revisión', ['revision_id' => $revision->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error al eliminar la revisión.']);
        }

        return redirect()->route('bikes.revisions.index', $bike->id)
            ->with('success', '🗑️ Revisión eliminada.');
    }
}
