<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/template/configs/bootstrap.php';

final class ApiRateLimiter
{
    public static function enforce(string $scope, string $identity = '', int $maxAttempts = 10, int $windowSeconds = 300): void
    {
        if ($maxAttempts < 1 || $windowSeconds < 1) {
            return;
        }

        $clientKey = self::clientKey();
        $identityKey = self::normalizeIdentity($identity);
        $key = hash('sha256', $scope . '|' . $clientKey . '|' . $identityKey);
        $dir = self::storageDir();

        if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
            error_log('[ApiRateLimiter] Could not create rate-limit directory.');
            return;
        }

        $file = $dir . DIRECTORY_SEPARATOR . $key . '.json';
        $now = time();
        $state = ['window_start' => $now, 'attempts' => 0];

        $handle = @fopen($file, 'c+');
        if (!$handle) {
            error_log('[ApiRateLimiter] Could not open rate-limit file.');
            return;
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                return;
            }

            $raw = stream_get_contents($handle);
            $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
            if (is_array($decoded)) {
                $state = [
                    'window_start' => (int)($decoded['window_start'] ?? $now),
                    'attempts' => (int)($decoded['attempts'] ?? 0),
                ];
            }

            if (($now - $state['window_start']) >= $windowSeconds) {
                $state = ['window_start' => $now, 'attempts' => 0];
            }

            $state['attempts']++;
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($state));
            fflush($handle);

            if ($state['attempts'] > $maxAttempts) {
                self::tooManyRequests(max(1, $windowSeconds - ($now - $state['window_start'])));
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function clientKey(): string
    {
        $remoteAddr = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $forwarded = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0] ?? '');
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }

        return $remoteAddr !== '' ? $remoteAddr : 'unknown';
    }

    private static function normalizeIdentity(string $identity): string
    {
        $identity = trim(strtolower($identity));
        if ($identity === '') {
            return 'anonymous';
        }

        return hash('sha256', $identity);
    }

    private static function storageDir(): string
    {
        $configured = getenv('API_RATE_LIMIT_DIR');
        if (is_string($configured) && trim($configured) !== '') {
            return rtrim($configured, "\\/");
        }

        return rtrim(sys_get_temp_dir(), "\\/") . DIRECTORY_SEPARATOR . 'laborow_api_rate_limits';
    }

    private static function tooManyRequests(int $retryAfter): never
    {
        http_response_code(429);
        header('Content-Type: application/json; charset=UTF-8');
        header('Retry-After: ' . $retryAfter);
        echo json_encode([
            'error' => true,
            'message' => 'Too many attempts. Please try again later.',
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
}
