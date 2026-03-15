<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\ImmigrationClandestine;
use App\Models\Infraction;
use App\Traits\ScopeByRole;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SurveillanceController extends Controller
{
    use ScopeByRole;
    // Centroides des régions du Sénégal (fallback quand pas de GPS)
    const REGION_COORDS = [
        'Dakar'        => [14.6928, -17.4467],
        'Thiès'        => [14.7910, -16.9256],
        'Diourbel'     => [14.6550, -16.2320],
        'Fatick'       => [14.3390, -16.4050],
        'Kaolack'      => [14.1490, -16.0726],
        'Kaffrine'     => [14.1050, -15.5510],
        'Louga'        => [15.6170, -16.2240],
        'Saint-Louis'  => [16.0179, -16.4896],
        'Matam'        => [15.6560, -13.2550],
        'Tambacounda'  => [13.7707, -13.6673],
        'Kédougou'     => [12.5569, -12.1747],
        'Kolda'        => [12.8954, -14.9413],
        'Sédhiou'      => [12.7080, -15.5570],
        'Ziguinchor'   => [12.5681, -16.2719],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user         = auth()->user();
        $isAdmin      = $user->hasRole(['super_admin', 'admin']);
        $scopeRegion  = $user->getRegionEffective();
        $scopeService = $user->service_id;

        $regions = $isAdmin
            ? array_keys(self::REGION_COORDS)
            : ($scopeRegion ? [$scopeRegion] : array_keys(self::REGION_COORDS));

        return view('surveillance.index', compact('regions', 'isAdmin', 'scopeRegion', 'scopeService'));
    }

    /**
     * Endpoint AJAX — retourne les marqueurs JSON
     */
    public function data(Request $request)
    {
        $markers = [];
        $now     = Carbon::now();

        // ── Filtres communs ──────────────────────────────────────────
        $region    = $request->region;
        $commune   = $request->commune;      // recherche dans localite
        $periode   = $request->periode;      // today | week | month | year
        $types     = $request->types ?? ['accidents', 'infractions', 'immigrations'];

        [$dateMin, $dateMax] = $this->parsePeriode($periode, $request);

        // ── Accidents ────────────────────────────────────────────────
        if (in_array('accidents', $types)) {
            $query = Accident::with('service');
            $this->applyScopeFilters($query);
            if ($region)  $query->where('region', $region);
            if ($commune) $query->where('localite', 'like', "%{$commune}%");
            if ($dateMin) $query->whereDate('date_accident', '>=', $dateMin);
            if ($dateMax) $query->whereDate('date_accident', '<=', $dateMax);

            $query->each(function ($a) use (&$markers, $now) {
                [$lat, $lng] = $this->resolveCoords(
                    $a->latitude, $a->longitude, $a->region
                );
                if (!$lat) return;

                $jours  = $now->diffInHours(Carbon::parse($a->date_accident));
                $color  = $this->couleurMarqueur($jours);

                $markers[] = [
                    'type'        => 'accident',
                    'id'          => $a->id,
                    'lat'         => (float) $lat,
                    'lng'         => (float) $lng,
                    'color'       => $color,
                    'titre'       => $a->numero_rapport,
                    'date'        => $a->date_accident->format('d/m/Y'),
                    'localite'    => $a->localite,
                    'region'      => $a->region,
                    'description' => $a->type_accident . ' — ' . $a->gravite,
                    'victimes'    => $a->nombre_victimes,
                    'statut'      => $a->statut,
                    'url'         => route('accidents.show', $a->id),
                    'has_gps'     => $a->latitude && $a->longitude,
                ];
            });
        }

        // ── Infractions ──────────────────────────────────────────────
        if (in_array('infractions', $types)) {
            $query = Infraction::with(['typeInfraction', 'service']);
            $this->applyScopeFilters($query);
            if ($region)  $query->where('region', $region);
            if ($commune) $query->where('localite', 'like', "%{$commune}%");
            if ($dateMin) $query->whereDate('date_infraction', '>=', $dateMin);
            if ($dateMax) $query->whereDate('date_infraction', '<=', $dateMax);

            $query->each(function ($inf) use (&$markers, $now) {
                [$lat, $lng] = $this->resolveCoords(null, null, $inf->region);
                if (!$lat) return;

                // Légère dispersion aléatoire pour éviter la superposition
                $lat += (mt_rand(-50, 50) / 10000);
                $lng += (mt_rand(-50, 50) / 10000);

                $jours  = $now->diffInHours(Carbon::parse($inf->date_infraction));
                $color  = $this->couleurMarqueur($jours);

                $markers[] = [
                    'type'        => 'infraction',
                    'id'          => $inf->id,
                    'lat'         => (float) $lat,
                    'lng'         => (float) $lng,
                    'color'       => $color,
                    'titre'       => $inf->numero_dossier,
                    'date'        => $inf->date_infraction->format('d/m/Y'),
                    'localite'    => $inf->localite,
                    'region'      => $inf->region,
                    'description' => $inf->typeInfraction->nom ?? 'Infraction',
                    'victimes'    => null,
                    'statut'      => $inf->statut,
                    'url'         => route('infractions.show', $inf->id),
                    'has_gps'     => false,
                ];
            });
        }

        // ── Immigration clandestine ──────────────────────────────────
        if (in_array('immigrations', $types)) {
            $query = ImmigrationClandestine::with('service');
            $this->applyScopeFilters($query);
            if ($region)  $query->where('region', $region);
            if ($commune) $query->where('localite', 'like', "%{$commune}%");
            if ($dateMin) $query->whereDate('date_interception', '>=', $dateMin);
            if ($dateMax) $query->whereDate('date_interception', '<=', $dateMax);

            $query->each(function ($imm) use (&$markers, $now) {
                [$lat, $lng] = $this->resolveCoords(
                    $imm->latitude, $imm->longitude, $imm->region
                );
                if (!$lat) return;

                $jours  = $now->diffInHours(Carbon::parse($imm->date_interception));
                $color  = $this->couleurMarqueur($jours);

                $markers[] = [
                    'type'        => 'immigration',
                    'id'          => $imm->id,
                    'lat'         => (float) $lat,
                    'lng'         => (float) $lng,
                    'color'       => $color,
                    'titre'       => $imm->numero_cas,
                    'date'        => $imm->date_interception->format('d/m/Y'),
                    'localite'    => $imm->localite,
                    'region'      => $imm->region,
                    'description' => ucfirst($imm->type_operation) . ' — ' . $imm->nombre_personnes . ' pers.',
                    'victimes'    => $imm->nombre_personnes,
                    'statut'      => $imm->statut,
                    'url'         => route('immigrations.show', $imm->id),
                    'has_gps'     => $imm->latitude && $imm->longitude,
                ];
            });
        }

        return response()->json([
            'markers' => $markers,
            'total'   => count($markers),
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function couleurMarqueur(int $heures): string
    {
        if ($heures < 24)  return 'red';
        if ($heures < 168) return 'orange';  // 7 jours
        return 'green';
    }

    private function resolveCoords($lat, $lng, ?string $region): array
    {
        if ($lat && $lng) return [$lat, $lng];
        if ($region && isset(self::REGION_COORDS[$region])) {
            return self::REGION_COORDS[$region];
        }
        return [null, null];
    }

    private function parsePeriode(?string $periode, Request $request): array
    {
        $now = Carbon::now();
        return match ($periode) {
            'today'  => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week'   => [$now->copy()->subDays(7), $now],
            'month'  => [$now->copy()->subMonth(), $now],
            'year'   => [$now->copy()->subYear(), $now],
            'custom' => [$request->date_debut, $request->date_fin],
            default  => [null, null],
        };
    }
}
