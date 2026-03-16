<?php

namespace App\Http\Controllers;

use App\Models\DemandeTransport;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;

class TransportController extends Controller
{
    use ScopeByRole;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin|admin|superviseur');
    }

    public function index(Request $request)
    {
        $query = DemandeTransport::with(['accident', 'demandeur', 'transporteur']);

        // Scoper par région/service via l'accident lié
        if (!$this->isGlobalAdmin()) {
            $user = auth()->user();
            $query->whereHas('accident', function ($q) use ($user) {
                if ($this->isRegionalAdmin()) {
                    $region = $user->getRegionEffective();
                    if ($region) $q->where('region', $region);
                } else {
                    if ($user->service_id) {
                        $q->where('service_id', $user->service_id);
                    } elseif ($user->getRegionEffective()) {
                        $q->where('region', $user->getRegionEffective());
                    }
                }
            });
        }

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('transporteur', fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhereHas('demandeur',   fn($u) => $u->where('name', 'like', "%$search%"))
                  ->orWhereHas('accident',    fn($a) => $a->where('localite', 'like', "%$search%"));
            });
        }

        $transports = $query->latest()->paginate(20);

        // Compteurs par statut (sur la même portée, sans pagination)
        $statsQuery = DemandeTransport::query();
        if (!$this->isGlobalAdmin()) {
            $user = auth()->user();
            $statsQuery->whereHas('accident', function ($q) use ($user) {
                if ($this->isRegionalAdmin()) {
                    $region = $user->getRegionEffective();
                    if ($region) $q->where('region', $region);
                } else {
                    if ($user->service_id) {
                        $q->where('service_id', $user->service_id);
                    } elseif ($user->getRegionEffective()) {
                        $q->where('region', $user->getRegionEffective());
                    }
                }
            });
        }

        $stats = [
            'total'      => (clone $statsQuery)->count(),
            'en_attente' => (clone $statsQuery)->where('statut', 'en_attente')->count(),
            'acceptee'   => (clone $statsQuery)->where('statut', 'acceptee')->count(),
            'en_cours'   => (clone $statsQuery)->where('statut', 'en_cours')->count(),
            'terminee'   => (clone $statsQuery)->where('statut', 'terminee')->count(),
            'annulee'    => (clone $statsQuery)->where('statut', 'annulee')->count(),
        ];

        return view('transports.index', compact('transports', 'stats'));
    }

    public function show(DemandeTransport $transport)
    {
        $transport->load(['accident', 'demandeur', 'transporteur']);
        return view('transports.show', compact('transport'));
    }

    /**
     * Endpoint AJAX — retourne la position live du transporteur pour l'admin.
     */
    public function livePosition(DemandeTransport $transport)
    {
        $transport->refresh();
        return response()->json([
            'statut'              => $transport->statut,
            'lat_transporteur'    => $transport->lat_transporteur,
            'lng_transporteur'    => $transport->lng_transporteur,
            'position_updated_at' => $transport->position_updated_at?->toISOString(),
        ]);
    }
}
