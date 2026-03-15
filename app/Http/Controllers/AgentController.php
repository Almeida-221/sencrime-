<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\MouvementAgent;
use App\Models\Service;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    public function index(Request $request)
    {
        $query = Agent::with('service');
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('matricule', 'like', '%' . $request->search . '%');
            });
        }
        $agents = $query->paginate(15);
        $services = Service::where('actif', true)->orderBy('nom')->get();
        return view('agents.index', compact('agents', 'services'));
    }

    public function create()
    {
        $services = Service::where('actif', true)->orderBy('nom')->get();
        return view('agents.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'matricule' => 'required|string|max:50|unique:agents',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
            'grade' => 'required|string|max:100',
            'fonction' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date',
            'service_id' => 'nullable|exists:services,id',
            'statut' => 'required|in:actif,inactif,suspendu,retraite',
        ]);

        Agent::create($request->all());

        return redirect()->route('agents.index')->with('success', 'Agent enregistré avec succès.');
    }

    public function show(Agent $agent)
    {
        $agent->load(['service', 'mouvements.serviceOrigine', 'mouvements.serviceDestination']);
        return view('agents.show', compact('agent'));
    }

    public function edit(Agent $agent)
    {
        $services = Service::where('actif', true)->orderBy('nom')->get();
        return view('agents.edit', compact('agent', 'services'));
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'matricule' => 'required|string|max:50|unique:agents,matricule,' . $agent->id,
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'genre' => 'required|in:M,F',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'nationalite' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
            'grade' => 'required|string|max:100',
            'fonction' => 'nullable|string|max:255',
            'date_recrutement' => 'nullable|date',
            'service_id' => 'nullable|exists:services,id',
            'statut' => 'required|in:actif,inactif,suspendu,retraite',
        ]);

        $oldServiceId = $agent->service_id;
        $agent->update($request->all());

        // Enregistrer le mouvement si le service a changé
        if ($oldServiceId != $request->service_id && $request->service_id) {
            MouvementAgent::create([
                'agent_id' => $agent->id,
                'service_origine_id' => $oldServiceId,
                'service_destination_id' => $request->service_id,
                'type_mouvement' => 'affectation',
                'date_mouvement' => now(),
                'motif' => 'Mise à jour du profil',
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('agents.index')->with('success', 'Agent mis à jour avec succès.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return redirect()->route('agents.index')->with('success', 'Agent supprimé avec succès.');
    }

    public function mouvement(Request $request, Agent $agent)
    {
        $request->validate([
            'service_destination_id' => 'required|exists:services,id',
            'type_mouvement' => 'required|in:affectation,mutation,detachement,retour',
            'date_mouvement' => 'required|date',
            'motif' => 'nullable|string|max:500',
        ]);

        MouvementAgent::create([
            'agent_id' => $agent->id,
            'service_origine_id' => $agent->service_id,
            'service_destination_id' => $request->service_destination_id,
            'type_mouvement' => $request->type_mouvement,
            'date_mouvement' => $request->date_mouvement,
            'motif' => $request->motif,
            'user_id' => auth()->id(),
        ]);

        $agent->update(['service_id' => $request->service_destination_id]);

        return redirect()->route('agents.show', $agent)->with('success', 'Mouvement enregistré avec succès.');
    }
}
