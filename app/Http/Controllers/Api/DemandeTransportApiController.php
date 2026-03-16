<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemandeTransport;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DemandeTransportApiController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = DemandeTransport::with(['accident.photos', 'demandeur', 'transporteur'])
            ->orderBy('created_at', 'desc');

        if ($user->hasRole('transporteur')) {
            $query->where(function ($q) use ($user) {
                $q->where('statut', 'en_attente')
                  ->orWhere('transporteur_id', $user->id);
            });
        } else {
            $query->where('demandeur_id', $user->id);
        }

        return response()->json($query->paginate(20));
    }

    public function show($id)
    {
        return response()->json(
            DemandeTransport::with(['accident.photos', 'demandeur', 'transporteur'])->findOrFail($id)
        );
    }

    public function accepter(Request $request, $id)
    {
        $demande = DemandeTransport::findOrFail($id);

        if ($demande->statut !== 'en_attente') {
            return response()->json(['message' => 'Demande déjà prise en charge'], 422);
        }

        $demande->update([
            'statut'          => 'acceptee',
            'transporteur_id' => $request->user()->id,
            'acceptee_at'     => now(),
        ]);

        $demande->load('accident', 'demandeur');

        // ── Notifier le demandeur ─────────────────────────────────────
        $transporteurName = $request->user()->name;
        NotificationService::send(
            $demande->demandeur_id,
            '✅ Transport accepté',
            "Votre demande de transport a été acceptée par {$transporteurName}",
            'transport_accepte', 'fa-check-circle', 'success',
            ['demande_id' => $demande->id]
        );

        return response()->json($demande);
    }

    public function enCours(Request $request, $id)
    {
        $demande = DemandeTransport::where('id', $id)
            ->where('transporteur_id', $request->user()->id)
            ->firstOrFail();

        $demande->update(['statut' => 'en_cours']);
        return response()->json($demande);
    }

    public function terminer(Request $request, $id)
    {
        $demande = DemandeTransport::where('id', $id)
            ->where('transporteur_id', $request->user()->id)
            ->firstOrFail();

        $demande->update(['statut' => 'terminee', 'terminee_at' => now()]);
        $demande->load('accident');

        // ── Notifier le demandeur ─────────────────────────────────────
        NotificationService::send(
            $demande->demandeur_id,
            '🏁 Transport terminé',
            'La course de transport a été complétée avec succès',
            'transport_termine', 'fa-flag-checkered', 'primary',
            ['demande_id' => $demande->id]
        );

        return response()->json($demande);
    }

    public function annuler(Request $request, $id)
    {
        $demande = DemandeTransport::findOrFail($id);
        $user    = $request->user();

        if ($demande->demandeur_id !== $user->id && $demande->transporteur_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $demande->update(['statut' => 'annulee']);
        return response()->json(['message' => 'Demande annulée']);
    }

    public function positionTransporteur(Request $request, $id)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $demande = DemandeTransport::where('id', $id)
            ->where('transporteur_id', $request->user()->id)
            ->firstOrFail();

        $demande->update([
            'lat_transporteur'    => $request->latitude,
            'lng_transporteur'    => $request->longitude,
            'position_updated_at' => now(),
        ]);

        return response()->json([
            'demande_id' => $demande->id,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'statut'     => $demande->statut,
        ]);
    }

    public function getPosition(Request $request, $id)
    {
        $demande = DemandeTransport::findOrFail($id);
        $user    = $request->user();

        // Only demandeur or the transporteur can see position
        if ($demande->demandeur_id !== $user->id && $demande->transporteur_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json([
            'demande_id'          => $demande->id,
            'latitude'            => $demande->lat_transporteur,
            'longitude'           => $demande->lng_transporteur,
            'position_updated_at' => $demande->position_updated_at?->toIso8601String(),
            'statut'              => $demande->statut,
        ]);
    }
}
