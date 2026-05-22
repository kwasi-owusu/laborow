<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/apps/template/statics/conn/anthrax.php';

$expectedIndexes = [
    ['users', 'idx_users_email', ['email']],
    ['users', 'idx_users_verification_code', ['verification_code']],
    ['users', 'idx_users_status', ['userStatus']],
    ['users', 'idx_users_device_token', ['current_device_token']],
    ['password_logs', 'idx_password_logs_user_password', ['user_id', 'password']],
    ['password_logs', 'idx_password_logs_user_date', ['user_id', 'system_date']],
    ['assets', 'idx_assets_status_date', ['asset_status', 'system_date']],
    ['assets', 'idx_assets_status_category', ['asset_status', 'category_ID']],
    ['assets', 'idx_assets_status_sub_category', ['asset_status', 'sub_category_ID']],
    ['assets', 'idx_assets_status_slug', ['asset_status', 'asset_slug']],
    ['assets', 'idx_assets_status_user', ['asset_status', 'user_ID']],
    ['assets', 'idx_assets_featured_status', ['is_featured', 'asset_status']],
    ['assets', 'idx_assets_status_rent_count', ['asset_status', 'total_rent_count']],
    ['assets', 'idx_assets_status_view_count', ['asset_status', 'total_view_count']],
    ['assets', 'idx_assets_user', ['user_ID']],
    ['asset_images', 'idx_asset_images_asset_id', ['asset_ID']],
    ['businesses', 'idx_businesses_user_id', ['user_id']],
    ['rent', 'idx_rent_asset_payment_ref', ['asset_id', 'payment_ref']],
    ['rent', 'idx_rent_user', ['user_ID']],
    ['rent', 'idx_rent_owner', ['asset_owner_ID']],
    ['rent', 'idx_rent_asset', ['asset_id']],
    ['rent', 'idx_rent_owner_returned', ['asset_owner_ID', 'is_returned']],
    ['rent', 'idx_rent_payment_status', ['pmt_status']],
    ['rent', 'idx_rent_status', ['rent_status']],
    ['rent', 'idx_rent_user_date', ['user_ID', 'system_date']],
    ['rent', 'idx_rent_owner_date', ['asset_owner_ID', 'system_date']],
    ['rent_requests', 'idx_rent_requests_user', ['user_ID']],
    ['rent_requests', 'idx_rent_requests_user_status', ['user_ID', 'request_status']],
    ['rent_request_images', 'idx_rent_request_images_rent_req_id', ['rent_req_id']],
    ['user_activities', 'idx_user_activities_user_date', ['user_id', 'system_date']],
];

$pdo = Connection::connect();
$failed = 0;
$missing = 0;
$present = 0;

foreach ($expectedIndexes as [$table, $indexName, $columns]) {
    if (!tableExists($pdo, $table)) {
        echo "[SKIP] {$table}.{$indexName} - table missing" . PHP_EOL;
        continue;
    }

    $missingColumns = missingColumns($pdo, $table, $columns);
    if ($missingColumns !== []) {
        $failed++;
        echo "[FAIL] {$table}.{$indexName} - missing column(s): " . implode(', ', $missingColumns) . PHP_EOL;
        continue;
    }

    if (indexExists($pdo, $table, $indexName)) {
        $present++;
        echo "[PASS] {$table}.{$indexName}" . PHP_EOL;
    } else {
        $missing++;
        echo "[MISS] {$table}.{$indexName}" . PHP_EOL;
    }
}

echo PHP_EOL . "Index check summary: {$present} present, {$missing} missing, {$failed} failed." . PHP_EOL;

if ($failed > 0 || $missing > 0) {
    exit(1);
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function missingColumns(PDO $pdo, string $table, array $columns): array
{
    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return array_values(array_filter($columns, static fn(string $column): bool => !in_array($column, $existing, true)));
}

function indexExists(PDO $pdo, string $table, string $indexName): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?');
    $stmt->execute([$table, $indexName]);
    return (int)$stmt->fetchColumn() > 0;
}
