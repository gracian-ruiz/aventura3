<?php

namespace App\Http\Controllers;
use Yajra\DataTables\DataTables;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $users = User::query();
            return DataTables::of($users)->make(true);
        }

        return response()->json(['error' => 'No autorizado'], 403);
    }

    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtiene el valor del buscador
    
        // Filtra por nombre o email si se ingresó un término de búsqueda
        $users = User::when($search, function ($query) use ($search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
        })->paginate(5); // Cambia el número de registros por página según lo que prefieras
    
        return view('users.index', compact('users', 'search'));
    }
    

    public function create()
    {
        return view('users.create');
    }

    public function store(UserRequest $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
