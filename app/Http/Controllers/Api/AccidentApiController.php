<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accident;
use App\Models\AccidentPhoto;
use App\Models\DemandeTransport;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AccidentApiController extends Controller
{
    // Retourne tous les types d'accidents distincts enregistrés en base
    public function typesAccidents()
    {
        $types = Accident::query()
            ->select('type_accident')
            ->distinct()
            ->whereNotNull('type_accident')
            ->orderBy('type_accident')
            ->pluck('type_accident');

        // Fusionner avec les types par défaut pour ne jamais avoir une liste vide
        $defaults = [
            'Collision frontale', 'Collision latérale', 'Renversement',
            'Chute de véhicule', 'Accident piéton', 'Accident moto',
            'Accident camion', 'Autre',
        ];

        $merged = collect($defaults)->merge($types)->unique()->sort()->values();

        return response()->json($merged);
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Accident::with(['photos', 'agent', 'service'])->orderBy('created_at', 'desc');

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
        if ($request->gravite) {
            $query->where('gravite', $request->gravite);
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_accident'   => 'required|date',
            'heure_accident'  => 'required',
            'localite'        => 'required|string',
            'region'          => 'required|string',
            'lieu_exact'      => 'required|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'type_accident'   => 'required|string',
            'description'     => 'required|string',
            'nombre_victimes' => 'required|integer|min:0',
            'nombre_blesses'  => 'required|integer|min:0',
            'nombre_morts'    => 'required|integer|min:0',
            'gravite'         => 'required|in:leger,grave,mortel',
            'photos.*'        => 'nullable|image|max:5120',
            'note_vocale'     => 'nullable|file|mimes:m4a,mp4,aac,ogg,webm,wav|max:20480',
        ]);

        $user = $request->user();
        $year = date('Y');
        // Utiliser withTrashed pour éviter les doublons avec les supprimés
        $lastNumero = Accident::withTrashed()
            ->whereYear('created_at', $year)
            ->max(DB::raw("CAST(SUBSTRING_INDEX(numero_rapport, '-', -1) AS UNSIGNED)"));
        $next = ($lastNumero ?? 0) + 1;
        $numero = 'ACC-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $accident = Accident::create([
                'numero_rapport'  => $numero,
                'date_accident'   => $request->date_accident,
                'heure_accident'  => $request->heure_accident,
                'localite'        => $request->localite,
                'region'          => $request->region,
                'lieu_exact'      => $request->lieu_exact,
                'latitude'        => $request->latitude,
                'longitude'       => $request->longitude,
                'type_accident'   => $request->type_accident,
                'description'     => $request->description,
                'nombre_victimes' => $request->nombre_victimes,
                'nombre_blesses'  => $request->nombre_blesses,
                'nombre_morts'    => $request->nombre_morts,
                'gravite'         => $request->gravite,
                'causes'          => $request->causes,
                'statut'          => 'ouvert',
                'service_id'      => $user->service_id,
                'user_id'         => $user->id,
                'observations'    => $request->observations,
                'note_vocale'     => $request->hasFile('note_vocale')
                    ? $request->file('note_vocale')->store('accidents/audio', 'public')
                    : null,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $i => $photo) {
                    $path = $photo->store('accidents/photos', 'public');
                    AccidentPhoto::create([
                        'accident_id'  => $accident->id,
                        'chemin'       => $path,
                        'nom_original' => $photo->getClientOriginalName(),
                        'ordre'        => $i,
                    ]);
                }
            }

            DB::commit();

            // ── Notifications ─────────────────────────────────────────
            $graviteLabel = ['leger' => 'Léger', 'grave' => 'Grave', 'mortel' => 'Mortel'][$accident->gravite] ?? $accident->gravite;
            NotificationService::notifyRegion(
                $accident->region,
                '🚨 Nouvel accident signalé',
                "Accident {$graviteLabel} à {$accident->localite} ({$accident->region}) — {$accident->nombre_victimes} victimes",
                'accident',
                'fa-car-crash',
                $accident->gravite === 'mortel' ? 'danger' : ($accident->gravite === 'grave' ? 'warning' : 'info'),
                ['accident_id' => $accident->id],
                "/accidents/{$accident->id}"
            );

            return response()->json($accident->load('photos'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $accident = Accident::with(['photos', 'agent', 'service', 'demandesTransport.transporteur'])->findOrFail($id);
        return response()->json($accident);
    }

    public function update(Request $request, $id)
    {
        $accident = Accident::findOrFail($id);
        $user     = $request->user();
        $diff     = now()->diffInMinutes($accident->created_at);

        if ($diff > 1 && !$user->hasRole('super_admin')) {
            return response()->json(['message' => 'Modification impossible après 1 minute'], 403);
        }

        $accident->update($request->only([
            'date_accident', 'heure_accident', 'localite', 'region', 'lieu_exact',
            'latitude', 'longitude', 'type_accident', 'description', 'nombre_victimes',
            'nombre_blesses', 'nombre_morts', 'gravite', 'causes', 'statut', 'observations',
        ]));

        return response()->json($accident->load('photos'));
    }

    public function destroy(Request $request, $id)
    {
        $accident = Accident::findOrFail($id);
        $diff     = now()->diffInMinutes($accident->created_at);

        if ($diff > 1 && !$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Suppression impossible après 1 minute'], 403);
        }

        foreach ($accident->photos as $photo) {
            Storage::disk('public')->delete($photo->chemin);
        }

        $accident->delete();
        return response()->json(['message' => 'Accident supprimé']);
    }

    public function demanderTransport(Request $request, $id)
    {
        $accident = Accident::findOrFail($id);
        $request->validate([
            'latitude_depart'  => 'required|numeric',
            'longitude_depart' => 'required|numeric',
        ]);

        $demande = DemandeTransport::create([
            'accident_id'      => $accident->id,
            'demandeur_id'     => $request->user()->id,
            'statut'           => 'en_attente',
            'latitude_depart'  => $request->latitude_depart,
            'longitude_depart' => $request->longitude_depart,
            'latitude_arrivee' => $accident->latitude,
            'longitude_arrivee'=> $accident->longitude,
            'notes'            => $request->notes,
        ]);

        // ── Notifier tous les transporteurs ───────────────────────────
        NotificationService::notifyTransporteurs(
            '🚑 Nouvelle demande de transport',
            "Transport requis pour accident à {$accident->localite} — Gravité: {$accident->gravite}",
            ['demande_id' => $demande->id, 'accident_id' => $accident->id],
        );

        return response()->json($demande->load('accident', 'demandeur'), 201);
    }
}
