<?php

namespace App\Http\Controllers;

use App\Models\TypeInfraction;
use Illuminate\Http\Request;

class TypeInfractionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    public function index(Request $request)
    {
        $query = TypeInfraction::withCount('infractions');
        if ($request->filled('categorie')) {
            $query->where('categorie', $request->categorie);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        $types = $query->paginate(15);
        return view('types_infractions.index', compact('types'));
    }

    public function create()
    {
        return view('types_infractions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:types_infractions',
            'description' => 'nullable|string',
            'categorie' => 'nullable|in:crime,delit,contravention',
            'amende_min' => 'nullable|numeric|min:0',
            'amende_max' => 'nullable|numeric|min:0|gte:amende_min',
        ]);

        TypeInfraction::create($request->all());

        return redirect()->route('types-infractions.index')->with('success', 'Type d\'infraction créé avec succès.');
    }

    public function show(TypeInfraction $typesInfraction)
    {
        $typesInfraction->loadCount('infractions');
        return view('types_infractions.show', compact('typesInfraction'));
    }

    public function edit(TypeInfraction $typesInfraction)
    {
        return view('types_infractions.edit', compact('typesInfraction'));
    }

    public function update(Request $request, TypeInfraction $typesInfraction)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:types_infractions,code,' . $typesInfraction->id,
            'description' => 'nullable|string',
            'categorie' => 'nullable|in:crime,delit,contravention',
            'amende_min' => 'nullable|numeric|min:0',
            'amende_max' => 'nullable|numeric|min:0',
            'actif' => 'boolean',
        ]);

        $typesInfraction->update(array_merge($request->all(), ['actif' => $request->has('actif')]));

        return redirect()->route('types-infractions.index')->with('success', 'Type d\'infraction mis à jour avec succès.');
    }

    public function destroy(TypeInfraction $typesInfraction)
    {
        if ($typesInfraction->infractions()->count() > 0) {
            return redirect()->route('types-infractions.index')->with('error', 'Impossible de supprimer un type avec des infractions associées.');
        }
        $typesInfraction->delete();
        return redirect()->route('types-infractions.index')->with('success', 'Type d\'infraction supprimé avec succès.');
    }
}
