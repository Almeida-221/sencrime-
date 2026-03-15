<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\DemandeTransport;
use App\Models\ImmigrationClandestine;
use App\Models\Infraction;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $accidentQuery    = Accident::query();
        $infractionQuery  = Infraction::query();
        $immigrationQuery = ImmigrationClandestine::query();

        if (!$user->hasRole('super_admin')) {
            if ($user->hasRole('superviseur')) {
                $region = $user->getRegionEffective();
                if ($region) {
                    $accidentQuery->where('region', $region);
                    $infractionQuery->where('region', $region);
                    $immigrationQuery->where('region', $region);
                }
            } else {
                if ($user->service_id) {
                    $accidentQuery->where('service_id', $user->service_id);
                    $infractionQuery->where('service_id', $user->service_id);
                    $immigrationQuery->where('service_id', $user->service_id);
                }
            }
        }

        return response()->json([
            'accidents' => [
                'total'   => (clone $accidentQuery)->count(),
                'ouverts' => (clone $accidentQuery)->where('statut', 'ouvert')->count(),
                'graves'  => (clone $accidentQuery)->where('gravite', 'grave')->count(),
                'mortels' => (clone $accidentQuery)->where('gravite', 'mortel')->count(),
            ],
            'infractions' => [
                'total'    => (clone $infractionQuery)->count(),
                'ouvertes' => (clone $infractionQuery)->where('statut', 'ouvert')->count(),
            ],
            'immigrations' => [
                'total'    => (clone $immigrationQuery)->count(),
                'ouvertes' => (clone $immigrationQuery)->where('statut', 'ouvert')->count(),
            ],
            'transports' => [
                'en_attente' => DemandeTransport::where('statut', 'en_attente')->count(),
                'en_cours'   => DemandeTransport::whereIn('statut', ['acceptee', 'en_cours'])->count(),
            ],
            'recents' => [
                'accidents'   => (clone $accidentQuery)->orderBy('created_at', 'desc')->take(5)
                    ->get(['id', 'numero_rapport', 'date_accident', 'localite', 'gravite', 'statut']),
                'infractions' => (clone $infractionQuery)->orderBy('created_at', 'desc')->take(5)
                    ->get(['id', 'numero_dossier', 'date_infraction', 'localite', 'statut']),
            ],
        ]);
    }
}
