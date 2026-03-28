<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amende;
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

        $user   = $request->user();
        $year   = date('Y');
        $prefix = 'INF-' . $year . '-';
        $maxNum = Infraction::withTrashed()
            ->whereYear('created_at', $year)
            ->where('numero_dossier', 'like', $prefix . '%')
            ->max('numero_dossier');
        $next   = $maxNum ? (intval(substr($maxNum, strlen($prefix))) + 1) : 1;
        $numero = $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);

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

        // ── Créer automatiquement l'amende correspondante ────────────
        $typeInfraction = TypeInfraction::find($infraction->type_infraction_id);
        $montant = $typeInfraction?->amende_min ?? 0;

        $ameYear   = date('Y');
        $amePrefix = 'AME-' . $ameYear . '-';
        $ameMax    = Amende::withTrashed()->whereYear('created_at', $ameYear)->where('numero_amende', 'like', $amePrefix . '%')->max('numero_amende');
        $ameNext   = $ameMax ? (intval(substr($ameMax, strlen($amePrefix))) + 1) : 1;
        $numeroAmende = $amePrefix . str_pad($ameNext, 5, '0', STR_PAD_LEFT);

        Amende::create([
            'numero_amende'         => $numeroAmende,
            'infraction_id'         => $infraction->id,
            'type_infraction_id'    => $infraction->type_infraction_id,
            'date_amende'           => $infraction->date_infraction,
            'date_echeance'         => now()->addDays(30)->toDateString(),
            'nom_contrevenant'      => $infraction->nom_contrevenant,
            'prenom_contrevenant'   => $infraction->prenom_contrevenant,
            'adresse_contrevenant'  => $infraction->adresse_contrevenant,
            'montant'               => $montant,
            'montant_paye'          => 0,
            'statut_paiement'       => 'en_attente',
            'localite'              => $infraction->localite,
            'region'                => $infraction->region,
            'service_id'            => $infraction->service_id,
            'user_id'               => $user->id,
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
