<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Infraction;
use App\Models\TypeInfraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InfractionApiController extends Controller
{
    public function typesInfractions()
    {
        return response()->json(TypeInfraction::where('actif', true)->get());
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Infraction::with(['typeInfraction', 'agent', 'service'])->orderBy('created_at', 'desc');

        if ($user->hasRole('super_admin')) {
            // all
        } elseif ($user->hasRole('superviseur')) {
            $region = $user->getRegionEffective();
            if ($region) {
                $query->where('region', $region);
            }
        } else {
            if ($user->service_id) {
                $query->where('service_id', $user->service_id);
            } elseif ($user->region) {
                $query->where('region', $user->region);
            }
        }

        if ($request->statut) {
            $query->where('statut', $request->statut);
        }
        if ($request->type_infraction_id) {
            $query->where('type_infraction_id', $request->type_infraction_id);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_infraction_id'  => 'required|exists:types_infractions,id',
            'date_infraction'     => 'required|date',
            'localite'            => 'required|string',
            'region'              => 'required|string',
            'description'         => 'required|string',
            'nom_contrevenant'    => 'required|string',
            'prenom_contrevenant' => 'required|string',
            'note_vocale'         => 'nullable|file|mimes:m4a,mp4,aac,ogg,webm,wav|max:20480',
        ]);

        $user  = $request->user();
        $year  = date('Y');
        $last  = Infraction::whereYear('created_at', $year)->count() + 1;
        $numero = 'INF-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);

        $infraction = Infraction::create([
            'numero_dossier'              => $numero,
            'type_infraction_id'          => $request->type_infraction_id,
            'date_infraction'             => $request->date_infraction,
            'localite'                    => $request->localite,
            'region'                      => $request->region,
            'description'                 => $request->description,
            'nom_contrevenant'            => $request->nom_contrevenant,
            'prenom_contrevenant'         => $request->prenom_contrevenant,
            'date_naissance_contrevenant' => $request->date_naissance_contrevenant,
            'nationalite_contrevenant'    => $request->nationalite_contrevenant,
            'adresse_contrevenant'        => $request->adresse_contrevenant,
            'statut'                      => 'ouvert',
            'service_id'                  => $user->service_id,
            'user_id'                     => $user->id,
            'observations'                => $request->observations,
            'note_vocale'                 => $request->hasFile('note_vocale')
                ? $request->file('note_vocale')->store('infractions/audio', 'public')
                : null,
        ]);

        return response()->json($infraction->load('typeInfraction'), 201);
    }

    public function show($id)
    {
        return response()->json(
            Infraction::with(['typeInfraction', 'agent', 'service', 'amendes'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $infraction = Infraction::findOrFail($id);
        $diff       = now()->diffInMinutes($infraction->created_at);

        if ($diff > 1 && !$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Modification impossible après 1 minute'], 403);
        }

        $infraction->update($request->only([
            'type_infraction_id', 'date_infraction', 'localite', 'region', 'description',
            'nom_contrevenant', 'prenom_contrevenant', 'date_naissance_contrevenant',
            'nationalite_contrevenant', 'adresse_contrevenant', 'statut', 'observations',
        ]));

        return response()->json($infraction->load('typeInfraction'));
    }

    public function destroy(Request $request, $id)
    {
        $infraction = Infraction::findOrFail($id);
        $diff       = now()->diffInMinutes($infraction->created_at);

        if ($diff > 1 && !$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Suppression impossible après 1 minute'], 403);
        }

        $infraction->delete();
        return response()->json(['message' => 'Infraction supprimée']);
    }
}
