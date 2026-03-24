<?php

namespace App\Console\Commands;

use App\Models\DemandeTransport;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ExpirerDemandesTransport extends Command
{
    protected $signature   = 'transport:expirer-demandes';
    protected $description = 'Expire toute demande de transport dépassant 7h (en attente OU en live)';

    public function handle(): void
    {
        $limite = now()->subHours(7);

        // 1. Demandes en attente depuis > 7h (jamais prises)
        $enAttente = DemandeTransport::where('statut', 'en_attente')
            ->where('created_at', '<', $limite)
            ->get();

        // 2. Courses actives (acceptee / en_cours) dont l'acceptation date de > 7h
        //    Si pas encore acceptée mais créée depuis > 7h, on se base sur created_at
        $enLive = DemandeTransport::whereIn('statut', ['acceptee', 'en_cours'])
            ->where(function ($q) use ($limite) {
                $q->where('acceptee_at', '<', $limite)
                  ->orWhere(function ($q2) use ($limite) {
                      $q2->whereNull('acceptee_at')
                         ->where('created_at', '<', $limite);
                  });
            })
            ->get();

        $toutes = $enAttente->merge($enLive);

        if ($toutes->isEmpty()) {
            $this->info('Aucune demande à expirer.');
            return;
        }

        foreach ($toutes as $demande) {
            $ancienStatut = $demande->statut;
            $demande->update([
                'statut'          => 'expiree',
                'transporteur_id' => null, // libérer le transporteur si en live
                'terminee_at'     => now(),
            ]);

            // Notifier le demandeur
            $msg = $ancienStatut === 'en_attente'
                ? 'Votre demande n\'a pas été prise en charge dans les 7 heures. Elle a été clôturée automatiquement.'
                : 'La course de transport a dépassé la limite de 7 heures et a été clôturée automatiquement.';

            NotificationService::send(
                $demande->demandeur_id,
                '⏰ Transport expiré (7h)',
                $msg,
                'transport_expire', 'fa-clock', 'secondary',
                ['demande_id' => $demande->id]
            );

            // Si le transporteur était en live, le notifier aussi
            if ($ancienStatut !== 'en_attente' && $demande->getOriginal('transporteur_id')) {
                NotificationService::send(
                    $demande->getOriginal('transporteur_id'),
                    '⏰ Course expirée (7h)',
                    'Cette course a dépassé la limite de 7 heures et a été clôturée automatiquement.',
                    'transport_expire', 'fa-clock', 'secondary',
                    ['demande_id' => $demande->id]
                );
            }

            $label = $ancienStatut === 'en_attente' ? 'en attente' : 'en live';
            $this->line("  Demande #{$demande->id} ({$label}) → expirée.");
        }

        $this->info("{$toutes->count()} demande(s) expirée(s).");
    }
}
