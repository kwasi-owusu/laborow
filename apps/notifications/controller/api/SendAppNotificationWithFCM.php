<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';

class FCMNotification
{
    private string $serviceAccountFile;
    private string $projectId;

    public function __construct()
    {
        $this->projectId = trim((string)(getenv('FIREBASE_PROJECT_ID') ?: '')) ?: 'tuetra-dba29';
        $this->serviceAccountFile = $this->resolveCredentialPath(trim((string)(getenv('FIREBASE_CREDENTIALS_PATH') ?: '')));

        if ($this->serviceAccountFile === '') {
            throw new RuntimeException('Firebase credentials path is not configured. Set FIREBASE_CREDENTIALS_PATH.');
        }

        if (!is_file($this->serviceAccountFile) || !is_readable($this->serviceAccountFile)) {
            throw new RuntimeException('Firebase credentials file is not readable.');
        }
    }

    private function resolveCredentialPath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            throw new RuntimeException('Firebase credentials path must be a server filesystem path, not a URL.');
        }

        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $isWindowsAbsolute = strlen($normalized) >= 3 && ctype_alpha($normalized[0]) && $normalized[1] === ':' && $normalized[2] === DIRECTORY_SEPARATOR;
        $isAbsolute = $isWindowsAbsolute || str_starts_with($normalized, DIRECTORY_SEPARATOR);

        if ($isAbsolute) {
            return $normalized;
        }

        return dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
    }

    public function send(string $deviceToken, string $title, string $body): array
    {
        $accessToken = $this->getAccessToken();

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body
                ],
                'android' => [
                    'priority' => 'HIGH'
                ]
            ]
        ];

        $ch = curl_init("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send");

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('FCM cURL Error: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['raw_response' => $response];
    }

    private function getAccessToken(): string
    {
        $jwt = $this->createJwt();

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt
            ])
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('OAuth cURL Error: ' . $error);
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!is_array($data) || !isset($data['access_token'])) {
            throw new RuntimeException('OAuth token request failed.');
        }

        return $data['access_token'];
    }

    private function createJwt(): string
    {
        $sa = json_decode(file_get_contents($this->serviceAccountFile), true);

        if (!is_array($sa) || empty($sa['client_email']) || empty($sa['private_key'])) {
            throw new RuntimeException('Firebase credentials file is invalid.');
        }

        $now = time();

        $header = $this->base64url(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));

        $payload = $this->base64url(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600
        ]));

        $signatureInput = "{$header}.{$payload}";

        if (!openssl_sign($signatureInput, $signature, $sa['private_key'], 'SHA256')) {
            throw new RuntimeException('Failed to sign Firebase JWT.');
        }

        return "{$signatureInput}." . $this->base64url($signature);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'This endpoint cannot be called directly.']);
    exit;
}
