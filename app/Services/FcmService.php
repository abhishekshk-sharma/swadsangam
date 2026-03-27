<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private string $projectId;
    private string $serviceAccountPath;

    public function __construct()
    {
        $this->projectId        = config('services.fcm.project_id', '');
        $this->serviceAccountPath = config('services.fcm.service_account_path',
            storage_path('app/firebase-service-account.json'));
    }

    /**
     * Send a notification to a single FCM token.
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($this->projectId) || empty($token)) return false;

        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) return false;

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => array_map('strval', $data),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id'    => 'swad_sangam_orders',
                            'default_sound' => true,
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);

            if (!$response->successful()) {
                Log::warning('FCM send failed', ['status' => $response->status(), 'body' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to multiple tokens (fan-out).
     */
    public function sendMulti(array $tokens, string $title, string $body, array $data = []): void
    {
        foreach (array_filter($tokens) as $token) {
            $this->send($token, $title, $body, $data);
        }
    }

    /**
     * Get OAuth2 access token from service account JSON using Google's token endpoint.
     */
    private function getAccessToken(): ?string
    {
        if (!file_exists($this->serviceAccountPath)) {
            Log::warning('FCM service account file not found: ' . $this->serviceAccountPath);
            return null;
        }

        $sa = json_decode(file_get_contents($this->serviceAccountPath), true);
        if (!$sa) return null;

        $now = time();
        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = "$header.$payload";
        openssl_sign($signingInput, $signature, $sa['private_key'], 'SHA256');
        $jwt = "$signingInput." . base64url_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        return $response->json('access_token');
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
