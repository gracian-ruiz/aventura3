<?php

namespace App\Http\Controllers;

use App\Models\AvisoEnviado;
use Illuminate\Http\Request;

class AvisoEnviadoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search'); // Obtiene el valor del buscador

        // Filtra por usuario, teléfono, bicicleta o componente
        $avisos = AvisoEnviado::with('user', 'bike', 'revision', 'componente')
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })->orWhere('telefono', 'LIKE', "%{$search}%")
                ->orWhereHas('bike', function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%");
                })->orWhereHas('componente', function ($q) use ($search) {
                    $q->where('nombre', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('enviado_en', 'desc')
            ->paginate(10);

        return view('avisos.index', compact('avisos', 'search'));
    }
}
