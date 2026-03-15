<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImmigrationClandestine;
use Illuminate\Http\Request;

class ImmigrationApiController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = ImmigrationClandestine::with(['agent', 'service'])->orderBy('created_at', 'desc');

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
        if ($request->type_operation) {
            $query->where('type_operation', $request->type_operation);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_interception'  => 'required|date',
            'localite'           => 'required|string',
            'region'             => 'required|string',
            'lieu_interception'  => 'required|string',
            'nombre_personnes'   => 'required|integer|min:1',
            'type_operation'     => 'required|in:interception,arrestation,rapatriement',
        ]);

        $user   = $request->user();
        $year   = date('Y');
        $last   = ImmigrationClandestine::whereYear('created_at', $year)->count() + 1;
        $numero = 'IMM-' . $year . '-' . str_pad($last, 5, '0', STR_PAD_LEFT);

        $imm = ImmigrationClandestine::create([
            'numero_cas'        => $numero,
            'date_interception' => $request->date_interception,
            'localite'          => $request->localite,
            'region'            => $request->region,
            'lieu_interception' => $request->lieu_interception,
            'latitude'          => $request->latitude,
            'longitude'         => $request->longitude,
            'nombre_personnes'  => $request->nombre_personnes,
            'nombre_hommes'     => $request->nombre_hommes ?? 0,
            'nombre_femmes'     => $request->nombre_femmes ?? 0,
            'nombre_mineurs'    => $request->nombre_mineurs ?? 0,
            'nationalites'      => $request->nationalites,
            'pays_origine'      => $request->pays_origine,
            'pays_destination'  => $request->pays_destination,
            'moyen_transport'   => $request->moyen_transport,
            'type_operation'    => $request->type_operation,
            'statut'            => 'ouvert',
            'description'       => $request->description,
            'service_id'        => $user->service_id,
            'user_id'           => $user->id,
            'observations'      => $request->observations,
        ]);

        return response()->json($imm, 201);
    }

    public function show($id)
    {
        return response()->json(
            ImmigrationClandestine::with(['agent', 'service'])->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $imm  = ImmigrationClandestine::findOrFail($id);
        $diff = now()->diffInMinutes($imm->created_at);

        if ($diff > 1 && !$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Modification impossible après 1 minute'], 403);
        }

        $imm->update($request->only([
            'date_interception', 'localite', 'region', 'lieu_interception', 'latitude', 'longitude',
            'nombre_personnes', 'nombre_hommes', 'nombre_femmes', 'nombre_mineurs',
            'nationalites', 'pays_origine', 'pays_destination', 'moyen_transport',
            'type_operation', 'statut', 'description', 'observations',
        ]));

        return response()->json($imm);
    }

    public function destroy(Request $request, $id)
    {
        $imm  = ImmigrationClandestine::findOrFail($id);
        $diff = now()->diffInMinutes($imm->created_at);

        if ($diff > 1 && !$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Suppression impossible après 1 minute'], 403);
        }

        $imm->delete();
        return response()->json(['message' => 'Cas supprimé']);
    }
}
