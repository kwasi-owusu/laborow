<?php

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    dirname(__DIR__, 3) . '/vendor/autoload.php',
];

$autoloadLoaded = false;
foreach ($autoloadCandidates as $file) {
    if (is_file($file)) {
        require_once $file;
        $autoloadLoaded = true;
        break;
    }
}
if (!$autoloadLoaded) {
    error_log('[bootstrap] Composer autoload not found. Proceeding without Composer packages.');
}

if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

// Optional: make getenv() consistent and set timezone if provided
foreach ($_ENV ?? [] as $k => $v) {
    putenv("$k=$v");
}
foreach ($_SERVER ?? [] as $k => $v) {
    if (is_string($v) && getenv($k) === false) {
        putenv("$k=$v");
    }
}
if (!empty($_ENV['APP_TIMEZONE'])) {
    date_default_timezone_set($_ENV['APP_TIMEZONE']);
}