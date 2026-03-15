<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\ImmigrationClandestine;
use App\Models\Service;
use App\Traits\ScopeByRole;
use Illuminate\Http\Request;

class ImmigrationClandestineController extends Controller
{
    use ScopeByRole;
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = ImmigrationClandestine::with(['service', 'agent']);
        $this->applyScopeFilters($query);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type_operation')) $query->where('type_operation', $request->type_operation);
        if ($request->filled('service_id')) $query->where('service_id', $request->service_id);
        if ($request->filled('date_debut')) $query->whereDate('date_interception', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date_interception', '<=', $request->date_fin);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_cas', 'like', '%' . $request->search . '%')
                  ->orWhere('localite', 'like', '%' . $request->search . '%')
                  ->orWhere('pays_origine', 'like', '%' . $request->search . '%');
            });
        }
        $cas = $query->latest()->paginate(15);
        $services = $this->scopedServices();
        $totalPersonnes = ImmigrationClandestine::sum('nombre_personnes');
        return view('immigrations.index', compact('cas', 'services', 'totalPersonnes'));
    }

    public function create()
    {
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $numeroCas = 'IMM-' . date('Y') . '-' . str_pad(ImmigrationClandestine::whereYear('created_at', date('Y'))->count() + 1, 5, '0', STR_PAD_LEFT);
        return view('immigrations.create', compact('services', 'agents', 'numeroCas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_cas' => 'required|string|max:50|unique:immigrations_clandestines',
            'date_interception' => 'required|date',
            'localite' => 'required|string|max:255',
            'nombre_personnes' => 'required|integer|min:1',
            'type_operation' => 'required|in:interception,arrestation,rapatriement',
            'statut' => 'required|in:ouvert,en_cours,ferme,rapatrie',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = array_merge($request->all(), ['user_id' => auth()->id()]);
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $request->region;
            $data['service_id'] = auth()->user()->service_id ?? $request->service_id;
        }
        ImmigrationClandestine::create($data);

        return redirect()->route('immigrations.index')->with('success', 'Cas d\'immigration enregistré avec succès.');
    }

    public function show(ImmigrationClandestine $immigration)
    {
        $immigration->load(['service', 'agent', 'user']);
        return view('immigrations.show', compact('immigration'));
    }

    public function edit(ImmigrationClandestine $immigration)
    {
        $services = $this->scopedServices();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        return view('immigrations.edit', compact('immigration', 'services', 'agents'));
    }

    public function update(Request $request, ImmigrationClandestine $immigration)
    {
        $request->validate([
            'numero_cas' => 'required|string|max:50|unique:immigrations_clandestines,numero_cas,' . $immigration->id,
            'date_interception' => 'required|date',
            'localite' => 'required|string|max:255',
            'nombre_personnes' => 'required|integer|min:1',
            'type_operation' => 'required|in:interception,arrestation,rapatriement',
            'statut' => 'required|in:ouvert,en_cours,ferme,rapatrie',
            'service_id' => 'nullable|exists:services,id',
            'agent_id' => 'nullable|exists:agents,id',
        ]);

        $data = $request->all();
        if (!auth()->user()->hasRole(['super_admin', 'admin'])) {
            $data['region']     = auth()->user()->getRegionEffective() ?? $immigration->region;
            $data['service_id'] = auth()->user()->service_id ?? $immigration->service_id;
        }
        $immigration->update($data);

        return redirect()->route('immigrations.show', $immigration)->with('success', 'Cas mis à jour avec succès.');
    }

    public function destroy(ImmigrationClandestine $immigration)
    {
        $immigration->delete();
        return redirect()->route('immigrations.index')->with('success', 'Cas supprimé avec succès.');
    }
}
