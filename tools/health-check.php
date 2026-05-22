<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/apps/template/statics/conn/anthrax.php';

$root = dirname(__DIR__);
$checks = [];

function ok(string $label): void
{
    global $checks;
    $checks[] = ['ok' => true, 'label' => $label, 'detail' => ''];
}

function fail(string $label, string $detail = ''): void
{
    global $checks;
    $checks[] = ['ok' => false, 'label' => $label, 'detail' => $detail];
}

function envValue(string $key): string
{
    $value = getenv($key);
    return $value === false ? '' : trim((string)$value);
}

function hasEnv(string $key): bool
{
    return envValue($key) !== '';
}

function checkRequiredEnv(array $keys): void
{
    $missing = [];
    foreach ($keys as $key) {
        if (!hasEnv($key)) {
            $missing[] = $key;
        }
    }

    if ($missing === []) {
        ok('Required env keys are present');
    } else {
        fail('Required env keys are present', 'Missing: ' . implode(', ', $missing));
    }
}

function resolveProjectPath(string $path): string
{
    global $root;
    $path = trim($path);
    if ($path === '') {
        return '';
    }

    $isWindowsAbsolute = strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/');
    $isUnixAbsolute = isset($path[0]) && $path[0] === DIRECTORY_SEPARATOR;
    if ($isWindowsAbsolute || $isUnixAbsolute) {
        return $path;
    }

    return $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
}

function checkExtension(string $extension, bool $required = true): void
{
    if (extension_loaded($extension)) {
        ok("PHP extension {$extension} loaded");
        return;
    }

    $message = $required ? 'Required extension missing' : 'Optional extension missing';
    fail("PHP extension {$extension} loaded", $message);
}

checkRequiredEnv([
    'APP_TIMEZONE',
    'DB_HOST',
    'DB_PORT',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'DB_CHARSET',
    'API_TOKEN_SECRET',
    'PAYSTACK_SECRET_KEY',
    'PAYSTACK_CURRENCY',
    'SMTP_HOST',
    'SMTP_PORT',
    'SMTP_USER',
    'SMTP_PASS',
    'SMTP_FROM_EMAIL',
    'FIREBASE_PROJECT_ID',
    'FIREBASE_CREDENTIALS_PATH',
]);

if (hasEnv('SECRET_KEY')) {
    ok('Legacy SECRET_KEY is present for compatibility');
} else {
    fail('Legacy SECRET_KEY is present for compatibility', 'Keep until all Paystack callers use PAYSTACK_SECRET_KEY only.');
}

checkExtension('pdo_mysql');
checkExtension('curl');
checkExtension('openssl');
checkExtension('json');
checkExtension('fileinfo');
checkExtension('gd', false);

try {
    $pdo = Connection::connect();
    $stmt = $pdo->query('SELECT 1');
    if ($stmt !== false && (int)$stmt->fetchColumn() === 1) {
        ok('Database connection works');
    } else {
        fail('Database connection works', 'SELECT 1 did not return expected result.');
    }
} catch (Throwable $e) {
    fail('Database connection works', 'Connection or query failed.');
}

$uploadDir = $root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'asset_images';
if (is_dir($uploadDir)) {
    ok('Asset upload directory exists');
    if (is_writable($uploadDir)) {
        ok('Asset upload directory is writable');
    } else {
        fail('Asset upload directory is writable');
    }

    $htaccess = $uploadDir . DIRECTORY_SEPARATOR . '.htaccess';
    if (is_file($htaccess)) {
        ok('Asset upload .htaccess exists');
    } else {
        fail('Asset upload .htaccess exists');
    }
} else {
    fail('Asset upload directory exists');
}

$firebasePath = resolveProjectPath(envValue('FIREBASE_CREDENTIALS_PATH'));
if ($firebasePath !== '' && is_file($firebasePath)) {
    ok('Firebase credentials file exists');
} else {
    fail('Firebase credentials file exists');
}

if (hasEnv('PAYSTACK_SECRET_KEY')) {
    ok('Paystack secret is configured');
} else {
    fail('Paystack secret is configured');
}

if (hasEnv('SMTP_HOST') && hasEnv('SMTP_USER') && hasEnv('SMTP_PASS')) {
    ok('SMTP config is present');
} else {
    fail('SMTP config is present');
}

$failed = 0;
foreach ($checks as $check) {
    $status = $check['ok'] ? 'PASS' : 'FAIL';
    $line = '[' . $status . '] ' . $check['label'];
    if (!$check['ok'] && $check['detail'] !== '') {
        $line .= ' - ' . $check['detail'];
    }
    echo $line . PHP_EOL;

    if (!$check['ok']) {
        $failed++;
    }
}

if ($failed > 0) {
    echo PHP_EOL . "Health check failed: {$failed} issue(s)." . PHP_EOL;
    exit(1);
}

echo PHP_EOL . 'Health check passed.' . PHP_EOL;
