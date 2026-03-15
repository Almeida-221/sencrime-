<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Amende;
use App\Models\ImmigrationClandestine;
use App\Models\Infraction;
use App\Models\TypeInfraction;
use App\Traits\ScopeByRole;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RapportController extends Controller
{
    use ScopeByRole;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $data = $this->buildRapportData($request);
        return view('rapports.index', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->buildRapportData($request);
        $pdf  = Pdf::loadView('rapports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'dpi'                   => 150,
                'defaultFont'           => 'DejaVu Sans',
                'isRemoteEnabled'       => false,
                'isHtml5ParserEnabled'  => true,
            ]);

        $filename = 'rapport_sencrime_' . ($data['periodeLabel'] ?? 'periode') . '_' . now()->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    private function buildRapportData(Request $request): array
    {
        $user        = auth()->user();
        $isAdmin     = $user->hasRole('super_admin');
        $isRegional  = $user->hasRole('superviseur');
        $region      = $user->getRegionEffective();

        // ── Période ──────────────────────────────────────────────────
        $periode = $request->input('periode', 'mois');
        [$dateDebut, $dateFin] = $this->getPeriode($periode, $request);
        $periodeLabel = $this->getPeriodeLabel($periode, $request, $dateDebut, $dateFin);

        // ── Scopes ───────────────────────────────────────────────────
        $scopeI = fn() => $this->applyScopeFilters(
            Infraction::whereBetween('date_infraction', [$dateDebut, $dateFin])
        );
        $scopeA = fn() => $this->applyScopeFilters(
            Accident::whereBetween('date_accident', [$dateDebut, $dateFin])
        );
        $scopeM = fn() => $this->applyScopeFilters(
            ImmigrationClandestine::whereBetween('date_interception', [$dateDebut, $dateFin])
        );

        // ── Statistiques générales ───────────────────────────────────
        $stats = [
            'total_infractions'   => $scopeI()->count(),
            'total_accidents'     => $scopeA()->count(),
            'total_immigrations'  => $scopeM()->count(),
            'total_victimes'      => $scopeA()->sum('nombre_victimes'),
            'total_morts'         => $scopeA()->sum('nombre_morts'),
            'total_blesses'       => $scopeA()->sum('nombre_blesses'),
            'total_migrants'      => $scopeM()->sum('nombre_personnes'),
            'accidents_mortels'   => $scopeA()->where('gravite', 'mortel')->count(),
            'accidents_graves'    => $scopeA()->where('gravite', 'grave')->count(),
        ];

        // ── Infractions par type ─────────────────────────────────────
        $infractionsParType = $scopeI()
            ->join('types_infractions', 'infractions.type_infraction_id', '=', 'types_infractions.id')
            ->select('types_infractions.nom', DB::raw('COUNT(*) as total'))
            ->groupBy('types_infractions.nom')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        // ── Accidents par gravité ────────────────────────────────────
        $accidentsParGravite = $scopeA()
            ->select('gravite', DB::raw('COUNT(*) as total'))
            ->groupBy('gravite')
            ->get()
            ->mapWithKeys(fn($r) => [$r->gravite => $r->total]);

        // ── Immigrations par pays ────────────────────────────────────
        $immigrationsParPays = $scopeM()
            ->select('pays_origine', DB::raw('COUNT(*) as total'), DB::raw('SUM(nombre_personnes) as total_personnes'))
            ->whereNotNull('pays_origine')
            ->groupBy('pays_origine')
            ->orderByDesc('total_personnes')
            ->limit(8)
            ->get();

        // ── Évolution mensuelle (données pour graphe) ────────────────
        $evolutionParMois = $this->getEvolutionMensuelle($dateDebut, $dateFin, $scopeI, $scopeA, $scopeM);

        // ── Par région (admin national seulement) ────────────────────
        $infractionsParRegion = null;
        $accidentsParRegion   = null;
        if ($isAdmin) {
            $infractionsParRegion = Infraction::whereBetween('date_infraction', [$dateDebut, $dateFin])
                ->select('region', DB::raw('COUNT(*) as total'))
                ->groupBy('region')->orderByDesc('total')->get();

            $accidentsParRegion = Accident::whereBetween('date_accident', [$dateDebut, $dateFin])
                ->select('region', DB::raw('COUNT(*) as total'))
                ->groupBy('region')->orderByDesc('total')->get();
        }

        // ── Détail par région — Infractions (tous rôles, scope appliqué) ──
        $infractionsParRegionDetail = $scopeI()
            ->select('region', DB::raw('COUNT(*) as total'))
            ->whereNotNull('region')
            ->groupBy('region')->orderByDesc('total')->get();

        // ── Détail par région — Accidents ────────────────────────────────
        $accidentsParRegionDetail = $scopeA()
            ->select(
                'region',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN gravite="mortel" THEN 1 ELSE 0 END) as mortels'),
                DB::raw('SUM(CASE WHEN gravite="grave"  THEN 1 ELSE 0 END) as graves'),
                DB::raw('SUM(CASE WHEN gravite="leger"  THEN 1 ELSE 0 END) as legers'),
                DB::raw('SUM(nombre_morts)   as morts'),
                DB::raw('SUM(nombre_blesses) as blesses'),
            )
            ->whereNotNull('region')
            ->groupBy('region')->orderByDesc('total')->get();

        // ── Détail par région — Immigration ──────────────────────────────
        $immigrationsParRegionDetail = $scopeM()
            ->select(
                'region',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(nombre_personnes) as personnes'),
                DB::raw('SUM(nombre_hommes)    as hommes'),
                DB::raw('SUM(nombre_femmes)    as femmes'),
                DB::raw('SUM(nombre_mineurs)   as mineurs'),
            )
            ->whereNotNull('region')
            ->groupBy('region')->orderByDesc('personnes')->get();

        // ── Top localités ────────────────────────────────────────────
        $topLocalitesInfractions = $scopeI()
            ->select('localite', DB::raw('COUNT(*) as total'))
            ->groupBy('localite')->orderByDesc('total')->limit(5)->get();

        $topLocalitesAccidents = $scopeA()
            ->select('localite', DB::raw('COUNT(*) as total'))
            ->groupBy('localite')->orderByDesc('total')->limit(5)->get();

        return compact(
            'stats', 'periode', 'periodeLabel', 'dateDebut', 'dateFin',
            'infractionsParType', 'accidentsParGravite', 'immigrationsParPays',
            'evolutionParMois', 'infractionsParRegion', 'accidentsParRegion',
            'topLocalitesInfractions', 'topLocalitesAccidents',
            'infractionsParRegionDetail', 'accidentsParRegionDetail', 'immigrationsParRegionDetail',
            'isAdmin', 'isRegional', 'region', 'user'
        );
    }

    private function getPeriode(string $periode, Request $request): array
    {
        return match($periode) {
            'jour'    => [now()->startOfDay(), now()->endOfDay()],
            'semaine' => [now()->startOfWeek(), now()->endOfWeek()],
            'mois'    => [now()->startOfMonth(), now()->endOfMonth()],
            'annee'   => [now()->startOfYear(), now()->endOfYear()],
            'custom'  => [
                Carbon::parse($request->input('date_debut', now()->startOfMonth()))->startOfDay(),
                Carbon::parse($request->input('date_fin', now()))->endOfDay(),
            ],
            default   => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function getPeriodeLabel(string $periode, Request $request, Carbon $debut, Carbon $fin): string
    {
        return match($periode) {
            'jour'   => 'Aujourd\'hui — ' . now()->format('d/m/Y'),
            'semaine'=> 'Semaine du ' . $debut->format('d/m') . ' au ' . $fin->format('d/m/Y'),
            'mois'   => $debut->translatedFormat('F Y'),
            'annee'  => 'Année ' . $debut->format('Y'),
            'custom' => 'Du ' . $debut->format('d/m/Y') . ' au ' . $fin->format('d/m/Y'),
            default  => $debut->translatedFormat('F Y'),
        };
    }

    private function getEvolutionMensuelle(Carbon $debut, Carbon $fin, callable $scopeI, callable $scopeA, callable $scopeM): array
    {
        $labels = [];
        $infractions = [];
        $accidents   = [];
        $migrations  = [];

        $current = $debut->copy()->startOfMonth();
        while ($current->lte($fin)) {
            $labels[]     = $current->translatedFormat('M Y');
            $moisDebut    = $current->copy()->startOfMonth();
            $moisFin      = $current->copy()->endOfMonth();

            $infractions[] = $scopeI()
                ->whereBetween('date_infraction', [$moisDebut, $moisFin])
                ->count();
            $accidents[] = $scopeA()
                ->whereBetween('date_accident', [$moisDebut, $moisFin])
                ->count();
            $migrations[] = $scopeM()
                ->whereBetween('date_interception', [$moisDebut, $moisFin])
                ->count();

            $current->addMonth();
        }

        return compact('labels', 'infractions', 'accidents', 'migrations');
    }
}
