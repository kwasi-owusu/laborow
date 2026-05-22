<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/apps/template/statics/conn/anthrax.php';

$pdo = Connection::connect();
$checks = [
    [
        'label' => 'Duplicate user emails',
        'sql' => "SELECT LOWER(TRIM(email)) AS value_key, COUNT(*) AS total FROM users WHERE email IS NOT NULL AND TRIM(email) <> '' GROUP BY LOWER(TRIM(email)) HAVING COUNT(*) > 1",
        'sensitive' => true,
    ],
    [
        'label' => 'Duplicate asset slugs',
        'sql' => "SELECT asset_slug AS value_key, COUNT(*) AS total FROM assets WHERE asset_slug IS NOT NULL AND TRIM(asset_slug) <> '' GROUP BY asset_slug HAVING COUNT(*) > 1",
        'sensitive' => false,
    ],
    [
        'label' => 'Duplicate Paystack payment references',
        'sql' => "SELECT payment_ref AS value_key, COUNT(*) AS total FROM rent WHERE payment_ref IS NOT NULL AND TRIM(payment_ref) <> '' GROUP BY payment_ref HAVING COUNT(*) > 1",
        'sensitive' => true,
    ],
];

$failed = 0;
foreach ($checks as $check) {
    try {
        $stmt = $pdo->query($check['sql']);
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        if (!$rows) {
            echo '[PASS] ' . $check['label'] . PHP_EOL;
            continue;
        }

        $failed++;
        echo '[WARN] ' . $check['label'] . ': ' . count($rows) . ' duplicate group(s)' . PHP_EOL;
        foreach (array_slice($rows, 0, 10) as $row) {
            $value = (string)($row['value_key'] ?? '');
            $display = $check['sensitive'] ? ('sha256:' . hash('sha256', $value)) : $value;
            echo '       ' . $display . ' count=' . (int)($row['total'] ?? 0) . PHP_EOL;
        }
        if (count($rows) > 10) {
            echo '       ...and ' . (count($rows) - 10) . ' more' . PHP_EOL;
        }
    } catch (Throwable $e) {
        $failed++;
        echo '[FAIL] ' . $check['label'] . ': query failed' . PHP_EOL;
    }
}

if ($failed > 0) {
    echo PHP_EOL . 'Duplicate check completed with warnings. Review before adding unique constraints.' . PHP_EOL;
    exit(1);
}

echo PHP_EOL . 'Duplicate check passed.' . PHP_EOL;