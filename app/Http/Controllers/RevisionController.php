<?php

namespace App\Http\Controllers;

use App\Models\Bike;
use App\Models\Revision;
use App\Models\Component;
use Illuminate\Http\Request;

class RevisionController extends Controller
{
    /**
     * Mostrar todas las revisiones de una bicicleta
     */

     public function allRevisions()
    {
        $revisions = Revision::with('bike', 'componente')->paginate(10);
        return view('revisions.index', compact('revisions'));
    }

    public function index(Bike $bike)
    {
        $revisions = $bike->revisions()->with('componente')->paginate(10);
        return view('revisions.index', compact('bike', 'revisions'));
    }
    
    /**
     * Formulario para añadir una nueva revisión
     */
    public function create(Bike $bike)
    {
        $componentes = Component::all();
        return view('revisions.create', compact('bike', 'componentes'));
    }

    /**
     * Guardar una nueva revisión
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

        return redirect()->route('bikes.revisions.index', ['bike' => $bike->id])
            ->with('success', '✅ Revisión añadida correctamente.');
    }
}
