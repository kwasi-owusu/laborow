<?php

require_once dirname(__DIR__, 2) . '/configs/bootstrap.php';

class Database
{
    private $database_name;
    private $database_user;
    private $database_pass;
    private $database_host;
    private $database_port;
    private $database_charset;
    private $database_link;

    function __construct()
    {
        $this->database_user = self::env('DB_USER');
        $this->database_pass = self::env('DB_PASS');
        $this->database_host = self::env('DB_HOST');
        $this->database_port = self::env('DB_PORT', '3306');
        $this->database_name = self::env('DB_NAME');
        $this->database_charset = self::env('DB_CHARSET', 'utf8mb4');
    }

    function Database()
    {
        self::__construct();
    }

    function changeUser($user)
    {
        $this->database_user = $user;
    }

    function changePass($pass)
    {
        $this->database_pass = $pass;
    }

    function changeHost($host)
    {
        $this->database_host = $host;
    }

    function changeName($name)
    {
        $this->database_name = $name;
    }

    function changeAll($user, $pass, $host, $name)
    {
        $this->database_user = $user;
        $this->database_pass = $pass;
        $this->database_host = $host;
        $this->database_name = $name;
    }

    function connect()
    {
        try {
            $this->assertConfigured();
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->database_host,
                $this->database_port,
                $this->database_name,
                $this->database_charset
            );

            return new PDO($dsn, $this->database_user, $this->database_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            error_log('[Database] DALCONN connection failed: ' . $e->getMessage());
            echo 'No Connection to Database';
            exit();
        }
    }

    private function assertConfigured(): void
    {
        $missing = [];
        foreach (['database_host', 'database_name', 'database_user', 'database_pass'] as $property) {
            if (trim((string)$this->$property) === '') {
                $missing[] = $property;
            }
        }

        if (!empty($missing)) {
            throw new RuntimeException('Missing database configuration.');
        }
    }

    private static function env(string $key, string $fallback = ''): string
    {
        $value = getenv($key);
        if ($value === false || trim((string)$value) === '') {
            return $fallback;
        }

        return trim((string)$value);
    }
}