<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function accessToken(): ?string
    {
        return Cache::remember('fcm_access_token_sencrime', 3500, function () {
            $keyPath = storage_path('firebase/service-account.json');
            if (!file_exists($keyPath)) {
                Log::error('FCM: service-account.json introuvable à ' . $keyPath);
                return null;
            }

            $sa  = json_decode(file_get_contents($keyPath), true);
            if (!$sa || empty($sa['private_key'])) {
                Log::error('FCM: service-account.json invalide ou corrompu');
                return null;
            }

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

            $token = $res->json('access_token');
            if (!$token) {
                Log::error('FCM: échec OAuth2', ['response' => $res->body()]);
            } else {
                Log::info('FCM: access token obtenu avec succès');
            }
            return $token;
        });
    }

    public static function send(string $token, string $title, string $body, array $data = []): void
    {
        try {
            $accessToken = self::accessToken();
            if (!$accessToken) {
                Log::error('FCM: pas de access token, envoi annulé');
                return;
            }

            $projectId = config('services.firebase.project_id');
            if (!$projectId) {
                Log::error('FCM: FIREBASE_PROJECT_ID non défini dans .env');
                return;
            }

            Log::info('FCM: envoi notification', [
                'token_prefix' => substr($token, 0, 30),
                'title'        => $title,
                'project_id'   => $projectId,
            ]);

            $response = Http::withHeaders([
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

            if ($response->successful()) {
                Log::info('FCM: notification envoyée ✓', ['message_id' => $response->json('name')]);
            } else {
                Log::error('FCM: erreur API', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                // Si token invalide → effacer le token en base
                if ($response->status() === 404 || str_contains($response->body(), 'UNREGISTERED')) {
                    \App\Models\User::where('fcm_token', $token)->update(['fcm_token' => null]);
                    Log::warning('FCM: token invalide supprimé de la base');
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM: exception', ['message' => $e->getMessage()]);
        }
    }

    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_filter(array_unique($tokens));
        if (empty($tokens)) {
            Log::warning('FCM: aucun token FCM disponible pour les transporteurs');
            return;
        }
        Log::info('FCM: envoi à ' . count($tokens) . ' transporteur(s)');
        foreach ($tokens as $token) {
            self::send((string) $token, $title, $body, $data);
        }
    }
}
