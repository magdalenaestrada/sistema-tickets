<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;

class SerieSucursalController extends Controller
{
    public function index(){
        $sucursales = Sucursal::where('estado', 'A');
        $tipo_docuemt
    }
}
