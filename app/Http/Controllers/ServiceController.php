<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    public function index()
    {
        $services = Service::withCount(['agents' => function ($q) {
            $q->where('statut', 'actif');
        }])->paginate(15);
        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services',
            'description' => 'nullable|string',
            'localite' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Service::create($request->all());

        return redirect()->route('services.index')->with('success', 'Service créé avec succès.');
    }

    public function show(Service $service)
    {
        $service->load(['agents' => function ($q) {
            $q->where('statut', 'actif');
        }]);
        $effectif = $service->agents->count();
        return view('services.show', compact('service', 'effectif'));
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'description' => 'nullable|string',
            'localite' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'actif' => 'boolean',
        ]);

        $service->update(array_merge($request->all(), ['actif' => $request->has('actif')]));

        return redirect()->route('services.index')->with('success', 'Service mis à jour avec succès.');
    }

    public function destroy(Service $service)
    {
        if ($service->agents()->count() > 0) {
            return redirect()->route('services.index')->with('error', 'Impossible de supprimer un service avec des agents.');
        }
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service supprimé avec succès.');
    }

    public function effectifs(Service $service)
    {
        $agents = $service->agents()->with('service')->paginate(15);
        return view('services.effectifs', compact('service', 'agents'));
    }
}
