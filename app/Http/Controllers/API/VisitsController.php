<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Visits;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitsController extends Controller
{
    public function getVisitStats()
    {
        // Récupération des 7 derniers jours
        $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 jours avant aujourd'hui
        $endDate = Carbon::now()->endOfDay(); // Aujourd'hui

        // Récupération et comptage des visites par jour
        $visits = Visits::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as visit')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Générer un tableau avec les 7 derniers jours
        $chartData = collect();
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->subDays(6 - $i)->toDateString(); // Date du jour i
            $visitCount = $visits->where('date', $date)->first()->visit ?? 0; // Vérifier s'il y a des visites

            $chartData->push([
                'name' => $date,
                'visit' => $visitCount,
            ]);
        }

        // Format de réponse
        return response()->json([
            'title' => 'Total des visites',
            'color' => '#FF8042',
            'dataKey' => 'visit',
            'chartData' => $chartData,
        ]);
    }
}
