<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Amende;
use App\Models\Infraction;
use App\Models\Service;
use App\Models\TypeInfraction;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;

class AmendeController extends Controller
{
    use ScopeByRole;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin|superviseur');
    }

    private function amendeScope(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->applyScopeFilters(Amende::query());
    }

    public function index(Request $request)
    {
        $query = $this->amendeScope()->with(['typeInfraction', 'service', 'agent']);

        if ($request->filled('statut_paiement')) $query->where('statut_paiement', $request->statut_paiement);
        if ($request->filled('service_id'))      $query->where('service_id', $request->service_id);
        if ($request->filled('date_debut'))      $query->whereDate('date_amende', '>=', $request->date_debut);
        if ($request->filled('date_fin'))        $query->whereDate('date_amende', '<=', $request->date_fin);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_amende', 'like', '%' . $request->search . '%')
                  ->orWhere('nom_contrevenant', 'like', '%' . $request->search . '%');
            });
        }

        $amendes  = $query->latest()->paginate(15);
        $services = $this->scopedServices();

        $statsQuery = $this->amendeScope();
        $stats = [
            'total_paye'   => (clone $statsQuery)->sum('montant_paye'),
            'total_impaye' => (clone $statsQuery)->sum('montant') - (clone $statsQuery)->sum('montant_paye'),
            'nb_amendes'   => (clone $statsQuery)->count(),
        ];

        return view('amendes.index', compact('amendes', 'services', 'stats'));
    }

    public function create()
    {
        $typesInfractions = TypeInfraction::where('actif', true)->orderBy('nom')->get();
        $services         = $this->scopedServices();
        $agents           = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $infractions      = $this->applyScopeFilters(Infraction::query())->orderBy('numero_dossier')->get();
        $ameYear = date('Y'); $amePrefix = 'AME-' . $ameYear . '-';
        $ameMax  = Amende::withTrashed()->whereYear('created_at', $ameYear)->where('numero_amende', 'like', $amePrefix . '%')->max('numero_amende');
        $numeroAmende = $amePrefix . str_pad($ameMax ? (intval(substr($ameMax, strlen($amePrefix))) + 1) : 1, 5, '0', STR_PAD_LEFT);
        return view('amendes.create', compact('typesInfractions', 'services', 'agents', 'infractions', 'numeroAmende'));
    }

    private function authorizeRegion(Amende $amende): void
    {
        if ($this->isGlobalAdmin()) return;

        $region = auth()->user()->getRegionEffective();
        if ($region && $amende->region && $amende->region !== $region) {
            abort(403, 'Accès refusé : cette amende n\'appartient pas à votre région.');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_amende' => 'required|string|max:50|unique:amendes',
            'date_amende' => 'required|date',
            'nom_contrevenant' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'statut_paiement' => 'required|in:impaye,partiel,paye',
            'montant_paye' => 'nullable|numeric|min:0',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        Amende::create(array_merge($request->all(), ['user_id' => auth()->id()]));

        return redirect()->route('amendes.index')->with('success', 'Amende enregistrée avec succès.');
    }

    public function show(Amende $amende)
    {
        $this->authorizeRegion($amende);
        $amende->load(['typeInfraction', 'infraction', 'service', 'agent', 'user']);
        return view('amendes.show', compact('amende'));
    }

    public function edit(Amende $amende)
    {
        $this->authorizeRegion($amende);
        $typesInfractions = TypeInfraction::where('actif', true)->orderBy('nom')->get();
        $services         = $this->scopedServices();
        $agents           = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $infractions      = $this->applyScopeFilters(Infraction::query())->orderBy('numero_dossier')->get();
        return view('amendes.edit', compact('amende', 'typesInfractions', 'services', 'agents', 'infractions'));
    }

    public function update(Request $request, Amende $amende)
    {
        $this->authorizeRegion($amende);
        $request->validate([
            'numero_amende' => 'required|string|max:50|unique:amendes,numero_amende,' . $amende->id,
            'date_amende' => 'required|date',
            'nom_contrevenant' => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'statut_paiement' => 'required|in:impaye,partiel,paye',
            'montant_paye' => 'nullable|numeric|min:0',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $amende->update($request->all());

        return redirect()->route('amendes.show', $amende)->with('success', 'Amende mise à jour avec succès.');
    }

    public function destroy(Amende $amende)
    {
        $this->authorizeRegion($amende);
        $amende->delete();
        return redirect()->route('amendes.index')->with('success', 'Amende supprimée avec succès.');
    }

    public function paiement(Request $request, Amende $amende)
    {
        $this->authorizeRegion($amende);
        $request->validate([
            'montant_paiement' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|string|max:50',
            'reference_paiement' => 'nullable|string|max:100',
        ]);

        $nouveauMontantPaye = $amende->montant_paye + $request->montant_paiement;
        $statut = $nouveauMontantPaye >= $amende->montant ? 'paye' : 'partiel';

        $amende->update([
            'montant_paye' => $nouveauMontantPaye,
            'statut_paiement' => $statut,
            'date_paiement' => now(),
            'mode_paiement' => $request->mode_paiement,
            'reference_paiement' => $request->reference_paiement,
        ]);

        return redirect()->route('amendes.show', $amende)->with('success', 'Paiement enregistré avec succès.');
    }
}
