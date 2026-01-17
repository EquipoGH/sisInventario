<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\TipoBien;
use App\Models\Bien;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Contar registros de cada tabla
        $totalAreas = Area::count();
        $totalTiposBien = TipoBien::count();
        $totalBienes = Bien::count();

        // Obtener los últimos bienes registrados
        // Obtener los últimos bienes registrados
        $ultimosBienes = Bien::with(['tipoBien'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();


        return view('admin.dashboard', compact(
            'totalAreas',
            'totalTiposBien',
            'totalBienes',
            'ultimosBienes'
        ));
    }
}
