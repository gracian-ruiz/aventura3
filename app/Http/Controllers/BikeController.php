<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BikeController extends Controller
{
    /**
     * Mostrar lista de bicicletas.
     */
    public function index()
    {
        $bikes = Bike::with('user')->paginate(10); // Paginación agregada
        return view('bikes.index', compact('bikes'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $users = User::all();
        return view('bikes.create', compact('users'));
    }

    /**
     * Guardar una nueva bicicleta.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'anio_modelo' => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        Bike::create([
            'user_id' => $request->user_id,
            'nombre' => $request->nombre,
            'marca' => $request->marca,
            'anio_modelo' => $request->anio_modelo,
        ]);

        return redirect()->route('bikes.index')->with('success', '🚴‍♂️ Bicicleta añadida correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Bike $bike)
    {
        $users = User::all();
        return view('bikes.edit', compact('bike', 'users'));
    }

    /**
     * Actualizar bicicleta.
     */
    public function update(Request $request, Bike $bike)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombre' => 'required|string|max:255',
            'marca' => 'required|string|max:255',
            'anio_modelo' => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        $bike->update($request->all());

        return redirect()->route('bikes.index')->with('success', '🚴‍♂️ Bicicleta actualizada correctamente.');
    }

    /**
     * Eliminar bicicleta.
     */
    public function destroy(Bike $bike)
    {
        $bike->delete();
        return redirect()->route('bikes.index')->with('success', '🗑️ Bicicleta eliminada.');
    }
}
