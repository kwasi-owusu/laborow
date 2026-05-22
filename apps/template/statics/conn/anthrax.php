<?php

require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

class Connection
{
    public static function connect(): PDO
    {
        try {
            $config = self::databaseConfig();
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['name'],
                $config['charset']
            );

            return new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('[Database] Connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Sorry, Connection Lost';
            exit();
        } catch (RuntimeException $e) {
            error_log('[Database] Configuration error: ' . $e->getMessage());
            http_response_code(500);
            echo 'Sorry, Connection Lost';
            exit();
        }
    }

    private static function databaseConfig(): array
    {
        $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        $missing = [];
        foreach ($required as $key) {
            if (!self::hasEnv($key)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException('Missing required env keys: ' . implode(', ', $missing));
        }

        return [
            'host' => self::env('DB_HOST'),
            'port' => self::env('DB_PORT', '3306'),
            'name' => self::env('DB_NAME'),
            'user' => self::env('DB_USER'),
            'pass' => self::env('DB_PASS'),
            'charset' => self::env('DB_CHARSET', 'utf8mb4'),
        ];
    }

    private static function env(string $key, string $fallback = ''): string
    {
        $value = getenv($key);
        if ($value === false || trim((string)$value) === '') {
            return $fallback;
        }

        return trim((string)$value);
    }

    private static function hasEnv(string $key): bool
    {
        $value = getenv($key);
        return $value !== false && trim((string)$value) !== '';
    }
}