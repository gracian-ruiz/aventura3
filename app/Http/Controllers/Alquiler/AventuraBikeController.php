<?php

namespace App\Http\Controllers\Alquiler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AventuraBikeController extends Controller
{
    public function bicismontaña(){

        return view('alquiler.aventurabike.montana');
    }
}
