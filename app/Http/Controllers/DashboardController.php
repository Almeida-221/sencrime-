<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Agent;
use App\Models\Amende;
use App\Models\ImmigrationClandestine;
use App\Models\Infraction;
use App\Models\Service;
use App\Models\ServiceRetribue;
use App\Models\TypeInfraction;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ScopeByRole;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user          = auth()->user();
        $isAdmin       = $user->hasRole(['super_admin']);
        $isAdminRegion = $user->hasRole(['superviseur']);
        $scopeRegion   = $user->getRegionEffective();
        $scopeService  = $user->service_id;

        // ── Helpers de scope ─────────────────────────────────────────
        $scopeInfraction  = fn() => $this->applyScopeFilters(Infraction::query());
        $scopeAccident    = fn() => $this->applyScopeFilters(Accident::query());
        $scopeImmigration = fn() => $this->applyScopeFilters(ImmigrationClandestine::query());

        // ── Stats générales ──────────────────────────────────────────
        $stats = [
            'total_infractions' => $scopeInfraction()->count(),
            'total_accidents'   => $scopeAccident()->count(),
            'total_immigrations'=> $scopeImmigration()->count(),
            'infractions_ouvertes' => $scopeInfraction()->where('statut', 'ouvert')->count(),
            'accidents_graves'     => $scopeAccident()->where('gravite', 'grave')->count()
                                     + $scopeAccident()->where('gravite', 'mortel')->count(),
            'immigrations_actives' => $scopeImmigration()->whereIn('statut', ['ouvert', 'en_cours'])->count(),
        ];

        // Stats admin seulement
        if ($isAdmin) {
            $stats += [
                'total_agents'           => Agent::where('statut', 'actif')->count(),
                'total_services'         => Service::where('actif', true)->count(),
                'total_amendes'          => Amende::count(),
                'amendes_impayees'       => Amende::where('statut_paiement', 'impaye')->count(),
                'montant_amendes_total'  => Amende::sum('montant'),
                'montant_amendes_paye'   => Amende::sum('montant_paye'),
                'total_services_retribues' => ServiceRetribue::count(),
            ];
        } elseif ($isAdminRegion && $scopeRegion) {
            $stats += [
                'total_services' => Service::where('actif', true)->where('region', $scopeRegion)->count(),
                'total_agents'   => Agent::where('statut', 'actif')
                    ->whereHas('service', fn($q) => $q->where('region', $scopeRegion))
                    ->count(),
            ];
        } elseif ($scopeService) {
            $stats += [
                'total_agents' => Agent::where('statut', 'actif')->where('service_id', $scopeService)->count(),
            ];
        }

        // ── Infractions par mois ─────────────────────────────────────
        $infractionsParMois = $scopeInfraction()
            ->select(
                DB::raw('MONTH(date_infraction) as mois'),
                DB::raw('YEAR(date_infraction) as annee'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('date_infraction', '>=', now()->subYear()->year)
            ->groupBy('annee', 'mois')
            ->orderBy('annee')->orderBy('mois')
            ->get();

        // ── Accidents par mois ───────────────────────────────────────
        $accidentsParMois = $scopeAccident()
            ->select(
                DB::raw('MONTH(date_accident) as mois'),
                DB::raw('YEAR(date_accident) as annee'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('date_accident', '>=', now()->subYear()->year)
            ->groupBy('annee', 'mois')
            ->orderBy('annee')->orderBy('mois')
            ->get();

        // ── Immigrations par mois ────────────────────────────────────
        $immigrationsParMois = $scopeImmigration()
            ->select(
                DB::raw('MONTH(date_interception) as mois'),
                DB::raw('YEAR(date_interception) as annee'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(nombre_personnes) as total_personnes')
            )
            ->whereYear('date_interception', '>=', now()->subYear()->year)
            ->groupBy('annee', 'mois')
            ->orderBy('annee')->orderBy('mois')
            ->get();

        // ── Infractions par type ─────────────────────────────────────
        $infractionsParType = TypeInfraction::withCount(['infractions' => function ($q) use ($scopeRegion, $scopeService, $isAdmin) {
            if (!$isAdmin) {
                if ($scopeService) $q->where('service_id', $scopeService);
                elseif ($scopeRegion) $q->where('region', $scopeRegion);
            }
        }])
        ->orderByDesc('infractions_count')
        ->take(10)
        ->get();

        // ── Infractions par localité ─────────────────────────────────
        $infractionsParLocalite = $scopeInfraction()
            ->select('localite', DB::raw('COUNT(*) as total'))
            ->groupBy('localite')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // ── Accidents par localité ────────────────────────────────────
        $accidentsParLocalite = $scopeAccident()
            ->select('localite', DB::raw('COUNT(*) as total'))
            ->groupBy('localite')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        // ── Dernières infractions ────────────────────────────────────
        $dernieresInfractions = $scopeInfraction()
            ->with(['typeInfraction', 'service'])
            ->latest()->take(5)->get();

        // ── Derniers accidents ───────────────────────────────────────
        $derniersAccidents = $scopeAccident()
            ->with('service')
            ->latest()->take(5)->get();

        // ── Amendes par statut (admin seulement) ─────────────────────
        $amendesParStatut = $isAdmin
            ? Amende::select('statut_paiement', DB::raw('COUNT(*) as total'), DB::raw('SUM(montant) as montant_total'))
                ->groupBy('statut_paiement')->get()
            : collect();

        return view('dashboard.index', compact(
            'stats',
            'infractionsParMois',
            'accidentsParMois',
            'infractionsParType',
            'infractionsParLocalite',
            'accidentsParLocalite',
            'dernieresInfractions',
            'derniersAccidents',
            'amendesParStatut',
            'immigrationsParMois',
            'isAdmin',
            'isAdminRegion',
            'scopeRegion',
            'scopeService',
        ));
    }
}
