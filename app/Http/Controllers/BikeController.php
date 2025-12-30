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
    public function index(Request $request)
    {
        $search = $request->input('search');

        $bikes = Bike::when($search, function ($query) use ($search) {
            return $query->where('nombre', 'LIKE', "%{$search}%")
                         ->orWhere('marca', 'LIKE', "%{$search}%");
        })->paginate(10);

        return view('bikes.index', compact('bikes', 'search'));
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
            'kilometros' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:100', // Validación para color
        ]);
    
        Bike::create([
            'user_id' => $request->user_id,
            'nombre' => $request->nombre,
            'marca' => $request->marca,
            'anio_modelo' => $request->anio_modelo,
            'kilometros' => $request->kilometros ?? 0,
            'color' => $request->color, // Añadido
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
            'kilometros' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:100',
        ]);
    
        $bike->update([
            'user_id' => $request->user_id,
            'nombre' => $request->nombre,
            'marca' => $request->marca,
            'anio_modelo' => $request->anio_modelo,
            'kilometros' => $request->kilometros ?? $bike->kilometros,
            'color' => $request->color,
        ]);
    
        // 🔹 Redirigir según el parámetro 'redirect_to'
        $redirectTo = $request->input('redirect_to', 'bikes.index');
        
        if ($redirectTo === 'user.bikes') {
            return redirect()->route('users.bikes', $bike->user_id)->with('success', '🚴‍♂️ Bicicleta actualizada correctamente.');
        }
        
        return redirect()->route('bikes.index')->with('success', '🚴‍♂️ Bicicleta actualizada correctamente.');
    }
    

    /**
     * Eliminar bicicleta.
     */
    public function destroy(Request $request, Bike $bike)
    {
        $userId = $bike->user_id;
        $bike->delete();
        
        // 🔹 Redirigir según el parámetro 'redirect_to'
        $redirectTo = $request->input('redirect_to', 'bikes.index');
        
        if ($redirectTo === 'user.bikes') {
            return redirect()->route('users.bikes', $userId)->with('success', '🗑️ Bicicleta eliminada.');
        }
        
        return redirect()->route('bikes.index')->with('success', '🗑️ Bicicleta eliminada.');
    }
}
