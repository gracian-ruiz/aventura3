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
        $search = $request->input('search');
    
        $users = User::with('bikes')
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('telefono', 'LIKE', "%{$search}%"); // <-- Nuevo filtro por teléfono
                });
            })
            ->paginate(10);
    
        return view('users.index', compact('users', 'search'));
    }
    
    
    

    public function create()
    {
        return view('users.create');
    }

    public function store(UserRequest $request)
    {
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);
    
            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Hubo un problema al crear el usuario: ' . $e->getMessage()])
                         ->withInput();
        }
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
