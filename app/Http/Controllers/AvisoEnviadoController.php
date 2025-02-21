<?php

namespace App\Http\Controllers;

use App\Models\AvisoEnviado;
use Illuminate\Http\Request;

class AvisoEnviadoController extends Controller
{
    public function index()
    {
        $avisos = AvisoEnviado::with('user', 'bike', 'revision', 'componente')
                    ->orderBy('enviado_en', 'desc')
                    ->paginate(10);

        return view('avisos.index', compact('avisos'));
    }
}
