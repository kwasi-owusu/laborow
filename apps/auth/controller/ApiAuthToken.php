<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/template/configs/bootstrap.php';
require_once __DIR__ . '/AuthEnums.php';

final class ApiAuthToken
{
    private const DEFAULT_TTL_SECONDS = 604800; // 7 days

    public static function issue(array $user, ?int $ttlSeconds = null): array
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + ($ttlSeconds ?? self::DEFAULT_TTL_SECONDS);

        $payload = [
            'user_id' => (int)($user['user_id'] ?? 0),
            'email' => (string)($user['email'] ?? ''),
            'user_type' => (string)($user['user_type'] ?? ''),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        return [
            'token' => self::encode($payload),
            'expires_at' => gmdate('c', $expiresAt),
        ];
    }

    public static function validate(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        [$payloadPart, $signature] = $parts;
        $expectedSignature = self::sign($payloadPart);
        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $payloadJson = self::base64UrlDecode($payloadPart);
        if ($payloadJson === false) {
            return null;
        }

        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) {
            return null;
        }

        if (empty($payload['user_id']) || empty($payload['exp']) || (int)$payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function bearerFromServer(array $server): ?string
    {
        $header = $server['HTTP_AUTHORIZATION']
            ?? $server['REDIRECT_HTTP_AUTHORIZATION']
            ?? $server['Authorization']
            ?? '';

        if (!is_string($header) || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        return trim(substr($header, 7));
    }

    private static function encode(array $payload): string
    {
        $payloadPart = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        return $payloadPart . '.' . self::sign($payloadPart);
    }

    private static function sign(string $payloadPart): string
    {
        return hash_hmac('sha256', $payloadPart, self::secret());
    }

    private static function secret(): string
    {
        static $resolvedSecret = null;
        if ($resolvedSecret !== null) {
            return $resolvedSecret;
        }

        $apiSecret = trim((string)(getenv('API_TOKEN_SECRET') ?: ''));
        if ($apiSecret !== '') {
            return $resolvedSecret = $apiSecret;
        }

        $legacySecret = trim((string)(getenv('SECRET_KEY') ?: ''));
        if ($legacySecret !== '') {
            error_log('[ApiAuthToken] API_TOKEN_SECRET is not configured; using legacy SECRET_KEY fallback.');
            return $resolvedSecret = $legacySecret;
        }

        error_log('[ApiAuthToken] API_TOKEN_SECRET is not configured; using built-in legacy fallback. Set API_TOKEN_SECRET in .env.');
        return $resolvedSecret = LaborowHashKeys::password_hash->value;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}