<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FCM v1 HTTP API — aucun package composer requis.
 * Utilise le service-account JSON pour obtenir un token OAuth2 court-lived.
 */
class FcmService
{
    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /** Retourne un token OAuth2 mis en cache (~1 heure). */
    private static function accessToken(): ?string
    {
        return Cache::remember('fcm_access_token_sencrime', 3500, function () {
            $keyPath = storage_path('firebase/service-account.json');
            if (!file_exists($keyPath)) {
                Log::warning('FCM: service-account.json introuvable à ' . $keyPath);
                return null;
            }

            $sa  = json_decode(file_get_contents($keyPath), true);
            $now = time();

            $header  = self::b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = self::b64url(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]));

            $toSign = "$header.$payload";
            openssl_sign($toSign, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256);
            $jwt = "$toSign." . self::b64url($signature);

            $res = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $res->json('access_token');
        });
    }

    /**
     * Envoyer une notification push à un token FCM.
     */
    public static function send(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = self::accessToken();
            if (!$accessToken) return;

            $projectId = config('services.firebase.project_id');
            if (!$projectId) {
                Log::warning('FCM: FIREBASE_PROJECT_ID non défini dans .env');
                return;
            }

            Http::withHeaders([
                'Authorization' => "Bearer $accessToken",
                'Content-Type'  => 'application/json',
            ])->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'message' => [
                        'token'        => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data' => array_map('strval', $data),
                        'android' => [
                            'priority'     => 'high',
                            'notification' => [
                                'channel_id'              => 'sencrime_transport',
                                'notification_priority'   => 'PRIORITY_HIGH',
                                'visibility'              => 'PUBLIC',
                                'default_sound'           => true,
                                'default_vibrate_timings' => true,
                                'default_light_settings'  => true,
                            ],
                        ],
                        'apns' => [
                            'headers' => ['apns-priority' => '10'],
                            'payload' => ['aps' => ['sound' => 'default', 'badge' => 1]],
                        ],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('FCM send failed: ' . $e->getMessage(), ['token' => substr($token, 0, 20)]);
        }
    }

    /**
     * Envoyer à plusieurs tokens FCM.
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach (array_filter(array_unique($tokens)) as $token) {
            self::send((string) $token, $title, $body, $data);
        }
    }
}
