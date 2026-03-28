<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Infraction;
use App\Models\Service;
use App\Models\TypeInfraction;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;

class InfractionController extends Controller
{
    use ScopeByRole;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Infraction::with(['typeInfraction', 'service', 'agent']);
        $this->applyScopeFilters($query);
        if ($request->filled('type_infraction_id')) {
            $query->where('type_infraction_id', $request->type_infraction_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        if ($request->filled('date_debut')) {
            $query->whereDate('date_infraction', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('date_infraction', '<=', $request->date_fin);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_dossier', 'like', '%' . $request->search . '%')
                  ->orWhere('nom_contrevenant', 'like', '%' . $request->search . '%')
                  ->orWhere('localite', 'like', '%' . $request->search . '%');
            });
        }
        $infractions = $query->latest()->paginate(15);
        $typesInfractions = TypeInfraction::where('actif', true)->orderBy('nom')->get();
        $services = $this->scopedServices();
        return view('infractions.index', compact('infractions', 'typesInfractions', 'services'));
    }

    public function create()
    {
        $typesInfractions = TypeInfraction::where('actif', true)->orderBy('nom')->get();
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $infYear = date('Y'); $infPrefix = 'INF-' . $infYear . '-';
        $infMax  = Infraction::withTrashed()->whereYear('created_at', $infYear)->where('numero_dossier', 'like', $infPrefix . '%')->max('numero_dossier');
        $numeroDossier = $infPrefix . str_pad($infMax ? (intval(substr($infMax, strlen($infPrefix))) + 1) : 1, 5, '0', STR_PAD_LEFT);
        return view('infractions.create', compact('typesInfractions', 'services', 'agents', 'numeroDossier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_dossier' => 'required|string|max:50|unique:infractions',
            'type_infraction_id' => 'required|exists:types_infractions,id',
            'date_infraction' => 'required|date',
            'localite' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'description' => 'required|string',
            'nom_contrevenant' => 'nullable|string|max:255',
            'prenom_contrevenant' => 'nullable|string|max:255',
            'statut' => 'required|in:ouvert,en_cours,ferme,classe',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = array_merge($request->all(), ['user_id' => auth()->id()]);
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $request->region;
            $data['service_id'] = auth()->user()->service_id ?? $request->service_id;
        }
        $infraction = Infraction::create($data);

        return redirect()->route('infractions.show', $infraction)->with('success', 'Infraction enregistrée avec succès.');
    }

    public function show(Infraction $infraction)
    {
        $infraction->load(['typeInfraction', 'service', 'agent', 'user', 'amendes']);
        return view('infractions.show', compact('infraction'));
    }

    public function edit(Infraction $infraction)
    {
        $typesInfractions = TypeInfraction::where('actif', true)->orderBy('nom')->get();
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        return view('infractions.edit', compact('infraction', 'typesInfractions', 'services', 'agents'));
    }

    public function update(Request $request, Infraction $infraction)
    {
        $request->validate([
            'numero_dossier' => 'required|string|max:50|unique:infractions,numero_dossier,' . $infraction->id,
            'type_infraction_id' => 'required|exists:types_infractions,id',
            'date_infraction' => 'required|date',
            'localite' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'description' => 'required|string',
            'nom_contrevenant' => 'nullable|string|max:255',
            'prenom_contrevenant' => 'nullable|string|max:255',
            'statut' => 'required|in:ouvert,en_cours,ferme,classe',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = $request->all();
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $infraction->region;
            $data['service_id'] = auth()->user()->service_id ?? $infraction->service_id;
        }
        $infraction->update($data);

        return redirect()->route('infractions.show', $infraction)->with('success', 'Infraction mise à jour avec succès.');
    }

    public function destroy(Infraction $infraction)
    {
        $infraction->delete();
        return redirect()->route('infractions.index')->with('success', 'Infraction supprimée avec succès.');
    }
}
