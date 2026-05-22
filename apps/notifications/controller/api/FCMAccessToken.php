<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';

class FCMAccessToken
{
    public function getAccessToken(): string
    {
        $serviceAccount = $this->loadServiceAccount();
        $tokenUri = $serviceAccount['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        $jwt = $this->createJwt($serviceAccount, $tokenUri);
        $response = $this->httpPost($tokenUri, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        if (!is_array($response) || empty($response['access_token'])) {
            throw new RuntimeException('Failed to obtain Firebase access token.');
        }

        return (string)$response['access_token'];
    }

    private function createJwt(array $serviceAccount, string $tokenUri): string
    {
        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        $claimSet = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $tokenUri,
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($claimSet));
        $signatureInput = $base64UrlHeader . '.' . $base64UrlPayload;

        if (!openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'SHA256')) {
            throw new RuntimeException('Failed to sign Firebase JWT.');
        }

        return $signatureInput . '.' . $this->base64UrlEncode($signature);
    }

    private function loadServiceAccount(): array
    {
        $path = $this->resolveCredentialPath(trim((string)(getenv('FIREBASE_CREDENTIALS_PATH') ?: '')));

        if ($path === '') {
            throw new RuntimeException('Firebase credentials path is not configured. Set FIREBASE_CREDENTIALS_PATH.');
        }

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Firebase credentials file is not readable.');
        }

        $serviceAccount = json_decode(file_get_contents($path), true);

        if (!is_array($serviceAccount) || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
            throw new RuntimeException('Firebase credentials file is invalid.');
        }

        return $serviceAccount;
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

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function httpPost(string $url, array $data): ?array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Firebase token cURL error: ' . $error);
        }

        curl_close($ch);

        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : null;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'This endpoint cannot be called directly.']);
    exit;
}
