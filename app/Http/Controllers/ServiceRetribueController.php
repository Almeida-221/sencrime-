<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Service;
use App\Models\ServiceRetribue;
use Illuminate\Http\Request;

class ServiceRetribueController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    public function index(Request $request)
    {
        $query = ServiceRetribue::with(['service']);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('statut_paiement')) $query->where('statut_paiement', $request->statut_paiement);
        if ($request->filled('service_id')) $query->where('service_id', $request->service_id);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('numero_mission', 'like', '%' . $request->search . '%')
                  ->orWhere('titre', 'like', '%' . $request->search . '%')
                  ->orWhere('client_nom', 'like', '%' . $request->search . '%');
            });
        }
        $missions = $query->latest()->paginate(15);
        $services = Service::where('actif', true)->orderBy('nom')->get();
        return view('services_retribues.index', compact('missions', 'services'));
    }

    public function create()
    {
        $services = Service::where('actif', true)->orderBy('nom')->get();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $misYear = date('Y'); $misPrefix = 'MIS-' . $misYear . '-';
        $misMax  = ServiceRetribue::withTrashed()->whereYear('created_at', $misYear)->where('numero_mission', 'like', $misPrefix . '%')->max('numero_mission');
        $numeroMission = $misPrefix . str_pad($misMax ? (intval(substr($misMax, strlen($misPrefix))) + 1) : 1, 5, '0', STR_PAD_LEFT);
        return view('services_retribues.create', compact('services', 'agents', 'numeroMission'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_mission' => 'required|string|max:50|unique:services_retribues',
            'titre' => 'required|string|max:255',
            'type_mission' => 'required|string|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'client_nom' => 'required|string|max:255',
            'montant_total' => 'required|numeric|min:0',
            'statut' => 'required|in:planifie,en_cours,termine,annule',
            'service_id' => 'nullable|exists:services,id',
            'agents' => 'nullable|array',
            'agents.*' => 'exists:agents,id',
        ]);

        $mission = ServiceRetribue::create(array_merge($request->except('agents'), ['user_id' => auth()->id()]));

        if ($request->filled('agents')) {
            $agentsData = [];
            foreach ($request->agents as $agentId) {
                $agentsData[$agentId] = [
                    'role' => $request->input('roles.' . $agentId, ''),
                    'remuneration' => $request->input('remunerations.' . $agentId, 0),
                ];
            }
            $mission->agents()->sync($agentsData);
        }

        return redirect()->route('services-retribues.show', $mission)->with('success', 'Mission enregistrée avec succès.');
    }

    public function show(ServiceRetribue $servicesRetribue)
    {
        $servicesRetribue->load(['service', 'agents', 'user']);
        return view('services_retribues.show', compact('servicesRetribue'));
    }

    public function edit(ServiceRetribue $servicesRetribue)
    {
        $services = Service::where('actif', true)->orderBy('nom')->get();
        $agents = Agent::where('statut', 'actif')->orderBy('nom')->get();
        $servicesRetribue->load('agents');
        return view('services_retribues.edit', compact('servicesRetribue', 'services', 'agents'));
    }

    public function update(Request $request, ServiceRetribue $servicesRetribue)
    {
        $request->validate([
            'numero_mission' => 'required|string|max:50|unique:services_retribues,numero_mission,' . $servicesRetribue->id,
            'titre' => 'required|string|max:255',
            'type_mission' => 'required|string|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'client_nom' => 'required|string|max:255',
            'montant_total' => 'required|numeric|min:0',
            'statut' => 'required|in:planifie,en_cours,termine,annule',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $servicesRetribue->update($request->except('agents'));

        if ($request->filled('agents')) {
            $agentsData = [];
            foreach ($request->agents as $agentId) {
                $agentsData[$agentId] = [
                    'role' => $request->input('roles.' . $agentId, ''),
                    'remuneration' => $request->input('remunerations.' . $agentId, 0),
                ];
            }
            $servicesRetribue->agents()->sync($agentsData);
        }

        return redirect()->route('services-retribues.show', $servicesRetribue)->with('success', 'Mission mise à jour avec succès.');
    }

    public function destroy(ServiceRetribue $servicesRetribue)
    {
        $servicesRetribue->delete();
        return redirect()->route('services-retribues.index')->with('success', 'Mission supprimée avec succès.');
    }

    public function paiement(Request $request, ServiceRetribue $servicesRetribue)
    {
        $request->validate([
            'montant_paiement' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|string|max:50',
        ]);

        $nouveauMontantPaye = $servicesRetribue->montant_paye + $request->montant_paiement;
        $statut = $nouveauMontantPaye >= $servicesRetribue->montant_total ? 'paye' : 'partiel';

        $servicesRetribue->update([
            'montant_paye' => $nouveauMontantPaye,
            'statut_paiement' => $statut,
            'date_paiement' => now(),
            'mode_paiement' => $request->mode_paiement,
        ]);

        return redirect()->route('services-retribues.show', $servicesRetribue)->with('success', 'Paiement enregistré avec succès.');
    }
}
