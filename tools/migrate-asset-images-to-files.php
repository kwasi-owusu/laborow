<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/apps/template/statics/conn/anthrax.php';

const IMAGE_UPLOAD_DIR = 'uploads/asset_images';
const MAX_IMAGE_BYTES = 2_097_152;

$options = getopt('', ['apply', 'limit::']);
if ($options === false) {
    $options = [];
}
$apply = array_key_exists('apply', $options);
$limit = isset($options['limit']) ? max(1, (int)$options['limit']) : 0;

$root = dirname(__DIR__);
$uploadRoot = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, IMAGE_UPLOAD_DIR);

if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0755, true)) {
    fwrite(STDERR, "Failed to create upload directory: {$uploadRoot}" . PHP_EOL);
    exit(1);
}

$pdo = Connection::connect();
$hasPathColumn = assetImagesHasPathColumn($pdo);
$sql = "SELECT asset_image_ID, asset_ID, asset_image_save FROM asset_images WHERE asset_image_save LIKE 'data:image/%;base64,%' ORDER BY asset_image_ID ASC";
if ($limit > 0) {
    $sql .= ' LIMIT ' . $limit;
}

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$converted = 0;
$skipped = 0;
$failed = 0;

if (!$apply) {
    echo "DRY RUN: no rows will be changed. Re-run with --apply to migrate." . PHP_EOL;
}

echo 'Rows found: ' . count($rows) . PHP_EOL;

foreach ($rows as $row) {
    $imageId = (int)$row['asset_image_ID'];
    $assetId = (int)$row['asset_ID'];
    $dataUri = (string)$row['asset_image_save'];

    try {
        $parsed = parseDataUriImage($dataUri);
        if (strlen($parsed['binary']) > MAX_IMAGE_BYTES) {
            throw new RuntimeException('Image exceeds max size.');
        }

        $relativePath = IMAGE_UPLOAD_DIR . '/' . $assetId . '/' . bin2hex(random_bytes(16)) . '.' . $parsed['extension'];
        $absoluteDir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, dirname($relativePath));
        $absolutePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            throw new RuntimeException('Could not create asset image directory.');
        }

        if ($apply) {
            if (file_put_contents($absolutePath, $parsed['binary'], LOCK_EX) === false) {
                throw new RuntimeException('Could not write image file.');
            }

            if ($hasPathColumn) {
                $update = $pdo->prepare('UPDATE asset_images SET asset_image_save = :path, asset_image_path = :path WHERE asset_image_ID = :id');
            } else {
                $update = $pdo->prepare('UPDATE asset_images SET asset_image_save = :path WHERE asset_image_ID = :id');
            }
            $update->execute([':path' => $relativePath, ':id' => $imageId]);
        }

        $converted++;
        echo ($apply ? 'MIGRATED' : 'WOULD MIGRATE') . " asset_image_ID={$imageId} asset_ID={$assetId} -> {$relativePath}" . PHP_EOL;
    } catch (Throwable $e) {
        $failed++;
        echo "FAILED asset_image_ID={$imageId}: " . $e->getMessage() . PHP_EOL;
    }
}

echo PHP_EOL;
echo "Summary: converted={$converted}, skipped={$skipped}, failed={$failed}" . PHP_EOL;
exit($failed > 0 ? 1 : 0);

function parseDataUriImage(string $dataUri): array
{
    if (!preg_match('#^data:image/(jpeg|png);base64,(.+)$#s', $dataUri, $matches)) {
        throw new RuntimeException('Unsupported or invalid data URI.');
    }

    $mimeType = strtolower($matches[1]);
    $binary = base64_decode($matches[2], true);
    if ($binary === false || $binary === '') {
        throw new RuntimeException('Invalid base64 image data.');
    }

    $imageInfo = @getimagesizefromstring($binary);
    if ($imageInfo === false) {
        throw new RuntimeException('Decoded data is not a valid image.');
    }

    $expectedMime = $mimeType === 'jpeg' ? 'image/jpeg' : 'image/png';
    if (($imageInfo['mime'] ?? '') !== $expectedMime) {
        throw new RuntimeException('Image content does not match declared MIME type.');
    }

    return [
        'binary' => $binary,
        'extension' => $mimeType === 'jpeg' ? 'jpg' : 'png',
    ];
}

function assetImagesHasPathColumn(PDO $pdo): bool
{
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM asset_images LIKE 'asset_image_path'");
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return false;
    }
}
