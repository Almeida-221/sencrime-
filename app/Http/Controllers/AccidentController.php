<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Agent;
use App\Models\Service;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;

class AccidentController extends Controller
{
    use ScopeByRole;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Accident::with(['service', 'agent']);
        $this->applyScopeFilters($query);
        if ($request->filled('gravite')) $query->where('gravite', $request->gravite);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('service_id')) $query->where('service_id', $request->service_id);
        if ($request->filled('date_debut')) $query->whereDate('date_accident', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date_accident', '<=', $request->date_fin);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_rapport', 'like', '%' . $request->search . '%')
                  ->orWhere('localite', 'like', '%' . $request->search . '%')
                  ->orWhere('type_accident', 'like', '%' . $request->search . '%');
            });
        }
        $accidents = $query->latest()->paginate(15);
        $services = $this->scopedServices();
        return view('accidents.index', compact('accidents', 'services'));
    }

    public function create()
    {
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $accYear = date('Y'); $accPrefix = 'ACC-' . $accYear . '-';
        $accMax  = Accident::withTrashed()->whereYear('created_at', $accYear)->where('numero_rapport', 'like', $accPrefix . '%')->max('numero_rapport');
        $numeroRapport = $accPrefix . str_pad($accMax ? (intval(substr($accMax, strlen($accPrefix))) + 1) : 1, 5, '0', STR_PAD_LEFT);
        return view('accidents.create', compact('services', 'agents', 'numeroRapport'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_rapport' => 'required|string|max:50|unique:accidents',
            'date_accident' => 'required|date',
            'localite' => 'required|string|max:255',
            'type_accident' => 'required|string|max:255',
            'description' => 'required|string',
            'nombre_victimes' => 'nullable|integer|min:0',
            'nombre_blesses' => 'nullable|integer|min:0',
            'nombre_morts' => 'nullable|integer|min:0',
            'gravite' => 'required|in:leger,grave,mortel',
            'statut' => 'required|in:ouvert,en_cours,ferme',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = array_merge($request->all(), ['user_id' => auth()->id()]);
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $request->region;
            $data['service_id'] = auth()->user()->service_id ?? $request->service_id;
        }
        $accident = Accident::create($data);

        return redirect()->route('accidents.show', $accident)->with('success', 'Accident enregistré avec succès.');
    }

    public function show(Accident $accident)
    {
        $accident->load(['service', 'agent', 'user', 'photos']);
        return view('accidents.show', compact('accident'));
    }

    public function edit(Accident $accident)
    {
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        return view('accidents.edit', compact('accident', 'services', 'agents'));
    }

    public function update(Request $request, Accident $accident)
    {
        $request->validate([
            'numero_rapport' => 'required|string|max:50|unique:accidents,numero_rapport,' . $accident->id,
            'date_accident' => 'required|date',
            'localite' => 'required|string|max:255',
            'type_accident' => 'required|string|max:255',
            'description' => 'required|string',
            'nombre_victimes' => 'nullable|integer|min:0',
            'nombre_blesses' => 'nullable|integer|min:0',
            'nombre_morts' => 'nullable|integer|min:0',
            'gravite' => 'required|in:leger,grave,mortel',
            'statut' => 'required|in:ouvert,en_cours,ferme',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = $request->all();
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $accident->region;
            $data['service_id'] = auth()->user()->service_id ?? $accident->service_id;
        }
        $accident->update($data);

        return redirect()->route('accidents.show', $accident)->with('success', 'Accident mis à jour avec succès.');
    }

    public function destroy(Accident $accident)
    {
        $accident->delete();
        return redirect()->route('accidents.index')->with('success', 'Accident supprimé avec succès.');
    }
}
