<?php

namespace App\Http\Controllers\Alquiler;

use App\Http\Controllers\Controller; 
use App\Models\UsuarioAlquiler;
use Illuminate\Http\Request;

class UsuarioAlquilerController extends Controller
{
    public function index(Request $request)
    {
        $query = UsuarioAlquiler::query();
    
        if ($request->has('search')) {
            $query->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('telefono', 'like', '%' . $request->search . '%');
        }
    
        $usuariosAlquiler = $query->paginate(10); // ✅ esto permite usar appends y links()
    
        return view('alquiler.usuarios_alquiler.index', compact('usuariosAlquiler'));
    }

    public function create()
    {
        return view('alquiler.usuarios_alquiler.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'dni' => 'required|string|unique:usuarios_alquiler,dni',
            'telefono' => 'required|string',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        UsuarioAlquiler::create($request->all());
        return redirect()->route('usuarios_alquiler.index')->with('success', 'Usuario creado');
    }

    public function edit(UsuarioAlquiler $usuario_alquiler)
    {
        return view('alquiler.usuarios_alquiler.edit', compact('usuario_alquiler'));
    }
    

    public function update(Request $request, UsuarioAlquiler $usuario_alquiler)
    {
        $request->validate([
            'nombre' => 'required|string',
            'telefono' => 'required|string',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);
    
        // Asignar solo los campos que deseas actualizar
        $usuario_alquiler->nombre = $request->nombre;
        $usuario_alquiler->telefono = $request->telefono;
        $usuario_alquiler->dni = $usuario_alquiler->dni;
        $usuario_alquiler->email = $request->email;
        $usuario_alquiler->direccion = $request->direccion;
    
        // No actualices el campo 'dni', ya que no quieres cambiarlo
        // $usuario->dni = $request->dni; // Esto lo omites
    
        // Guardar los cambios
        $usuario_alquiler->save();
    
        // Redirigir con mensaje de éxito
        return redirect()->route('usuarios_alquiler.index')->with('success', 'Usuario actualizado');
    }
    
    
    

    public function destroy(UsuarioAlquiler $usuario_alquiler)
    {
        $usuario_alquiler->delete();
        return redirect()->route('usuarios_alquiler.index')->with('success', 'Usuario eliminado');
    }
}
