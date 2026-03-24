<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\FcmService;

class NotificationService
{
    /**
     * Envoyer une notification à un utilisateur spécifique.
     */
    public static function send(
        int $userId,
        string $titre,
        string $message,
        string $type = 'system',
        string $icone = 'fa-bell',
        string $couleur = 'primary',
        ?array $data = null,
        ?string $lien = null
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $userId,
            'titre'   => $titre,
            'message' => $message,
            'type'    => $type,
            'icone'   => $icone,
            'couleur' => $couleur,
            'data'    => $data,
            'lien'    => $lien,
        ]);
    }

    /**
     * Envoyer à plusieurs utilisateurs.
     */
    public static function sendToMany(
        array $userIds,
        string $titre,
        string $message,
        string $type = 'system',
        string $icone = 'fa-bell',
        string $couleur = 'primary',
        ?array $data = null,
        ?string $lien = null
    ): void {
        $now  = now();
        $rows = array_map(fn($uid) => [
            'user_id'    => $uid,
            'titre'      => $titre,
            'message'    => $message,
            'type'       => $type,
            'icone'      => $icone,
            'couleur'    => $couleur,
            'data'       => $data ? json_encode($data) : null,
            'lien'       => $lien,
            'lu'         => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], array_unique($userIds));

        if (!empty($rows)) {
            DB::table('app_notifications')->insert($rows);
        }
    }

    /**
     * Notifier tous les superviseurs d'une région + super_admins.
     */
    public static function notifyRegion(
        string $region,
        string $titre,
        string $message,
        string $type,
        string $icone,
        string $couleur,
        ?array $data = null,
        ?string $lien = null
    ): void {
        $userIds = User::role(['super_admin', 'superviseur'])
            ->where(function ($q) use ($region) {
                $q->whereNull('region')  // super_admin sans région = national
                  ->orWhere('region', $region);
            })
            ->pluck('id')
            ->toArray();

        self::sendToMany($userIds, $titre, $message, $type, $icone, $couleur, $data, $lien);
    }

    /**
     * Notifier tous les transporteurs actifs (in-app + push FCM).
     */
    public static function notifyTransporteurs(
        string $titre,
        string $message,
        ?array $data = null,
        ?string $lien = null
    ): void {
        $transporteurs = User::role('transporteur')->get(['id', 'fcm_token']);
        $userIds = $transporteurs->pluck('id')->toArray();

        // Notification in-app (base de données)
        self::sendToMany($userIds, $titre, $message, 'transport_demande', 'fa-ambulance', 'danger', $data, $lien);

        // Push FCM sur les appareils (même hors connexion, livré dès retour réseau)
        $fcmTokens = $transporteurs->pluck('fcm_token')->filter()->values()->toArray();
        if (!empty($fcmTokens)) {
            FcmService::sendToTokens($fcmTokens, $titre, $message, $data ?? []);
        }
    }
}
