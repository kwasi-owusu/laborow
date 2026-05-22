<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

final class MDLSaveThisListing
{
    private const IMAGE_UPLOAD_DIR = 'uploads/asset_images';
    private const ALLOWED_IMAGE_TYPES = ['jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp'];

    public static function SaveThisListingMDL(string $tbl, string $tbl_b, array $data): bool
    {
        try {
            $pdo = Connection::connect();
            $pdo->beginTransaction();

            // Convert asset_properties to JSON safely
            $asset_properties = is_array($data['asp'])
                ? json_encode($data['asp'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                : (string)$data['asp'];

            if ($asset_properties === false) {
                throw new RuntimeException('Failed to encode asset properties to JSON.');
            }

            $stmt = $pdo->prepare("
                INSERT INTO $tbl (
                    user_ID, category_ID, sub_category_ID, asset_title,
                    asset_description, asset_properties, asset_slug, asset_key, asset_condition,
                    assets_location_longitude, asset_location_latitude,
                    charge_type, rent_amount, manu_date
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['user'],
                $data['lct'],
                $data['lst'],
                $data['title'],
                $data['asd'],
                $asset_properties,
                $data['slg'],
                $data['key'],
                $data['acd'],
                $data['lng'],
                $data['lat'],
                $data['cgt'],
                $data['rent_amount'],
                $data['md'],
            ]);

            $listingID = (int)$pdo->lastInsertId();
            self::insertAssetImages($pdo, $tbl_b, $listingID, $data['imgs'] ?? []);

            $pdo->commit();
            return true;
        } catch (PDOException | RuntimeException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('Listing Save Error: ' . $e->getMessage());
            return false;
        }
    }

    public static function UpdateThisListingMDL(string $tbl, string $tbl_b, array $data): bool
    {
        try {
            $pdo = Connection::connect();
            $pdo->beginTransaction();

            $ownerCheck = $pdo->prepare("SELECT asset_properties FROM $tbl WHERE assets_ID = :asd AND user_ID = :u LIMIT 1");
            $ownerCheck->execute([
                ':asd' => $data['assets_ID'],
                ':u'   => $data['user']
            ]);
            $existingAsset = $ownerCheck->fetch(PDO::FETCH_ASSOC);
            if (!$existingAsset) {
                throw new RuntimeException('Asset not found or not owned by user.');
            }

            // Preserve existing asset_properties when older mobile clients do not send it.
            if (array_key_exists('asp', $data) && $data['asp'] !== null && $data['asp'] !== '') {
                $asset_properties = is_array($data['asp'])
                    ? json_encode($data['asp'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : (string)$data['asp'];
            } else {
                $asset_properties = $existingAsset['asset_properties'] ?? null;
            }

            if ($asset_properties === false) {
                throw new RuntimeException('Failed to encode asset properties to JSON.');
            }

            $stmt = $pdo->prepare("
                UPDATE $tbl
                SET category_ID = :ct, sub_category_ID = :sct, asset_title = :ast,
                    asset_description = :adc, asset_condition = :cnd,
                    asset_properties = :asp,
                    assets_location_longitude = :lng, asset_location_latitude = :lat,
                    charge_type = :cgt, rent_amount = :amt, manu_date = :dt,
                    asset_key = :akey, asset_slug = :slug
                WHERE assets_ID = :asd AND user_ID = :u
            ");

            $stmt->execute([
                ':ct' => $data['lct'],
                ':sct' => $data['lst'],
                ':ast' => $data['title'],
                ':adc' => $data['asd'],
                ':cnd' => $data['acd'],
                ':asp' => $asset_properties,
                ':lng' => $data['lng'],
                ':lat' => $data['lat'],
                ':cgt' => $data['cgt'],
                ':amt' => $data['rent_amount'],
                ':dt' => $data['md'],
                ':akey' => $data['key'],
                ':slug' => $data['slg'],
                ':asd' => $data['assets_ID'],
                ':u' => $data['user']
            ]);

            $existingImages = [];
            // Refresh images only when new images are supplied, after the ownership check passes.
            if (!empty($data['imgs'])) {
                $existingImages = self::fetchImagePaths($pdo, $tbl_b, (int)$data['assets_ID']);
                $pdo->prepare("DELETE FROM $tbl_b WHERE asset_ID = ?")->execute([$data['assets_ID']]);
                self::insertAssetImages($pdo, $tbl_b, (int)$data['assets_ID'], $data['imgs']);
            }

            $pdo->commit();
            self::deleteImageFiles($existingImages);
            return true;
        } catch (PDOException | RuntimeException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('Listing Update Error: ' . $e->getMessage());
            return false;
        }
    }

    private static function insertAssetImages(PDO $pdo, string $tbl_b, int $assetId, array $images): void
    {
        if (empty($images)) {
            return;
        }

        $hasPathColumn = self::assetImagesHasPathColumn($pdo, $tbl_b);
        $sql = $hasPathColumn
            ? "INSERT INTO $tbl_b (asset_ID, asset_image_save, asset_image_path) VALUES (?, ?, ?)"
            : "INSERT INTO $tbl_b (asset_ID, asset_image_save) VALUES (?, ?)";
        $imgStmt = $pdo->prepare($sql);

        foreach ($images as $image) {
            $path = self::saveImageToFile($assetId, $image);
            if ($hasPathColumn) {
                $imgStmt->execute([$assetId, $path, $path]);
            } else {
                $imgStmt->execute([$assetId, $path]);
            }
        }
    }

    private static function saveImageToFile(int $assetId, array $image): string
    {
        $mimeType = strtolower((string)($image['mime_type'] ?? ''));
        if (!isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
            throw new RuntimeException('Invalid image MIME type.');
        }

        $base64 = (string)($image['base64'] ?? '');
        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Invalid image data.');
        }

        $imageInfo = @getimagesizefromstring($binary);
        if ($imageInfo === false) {
            throw new RuntimeException('Uploaded image data is not a valid image.');
        }

        $expectedMimeTypes = [
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        $expectedMime = $expectedMimeTypes[$mimeType] ?? '';
        if (($imageInfo['mime'] ?? '') !== $expectedMime) {
            throw new RuntimeException('Image MIME type does not match image content.');
        }

        $relativeDir = self::IMAGE_UPLOAD_DIR . '/' . $assetId;
        $absoluteDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            throw new RuntimeException('Failed to create image upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_IMAGE_TYPES[$mimeType];
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($absolutePath, $binary, LOCK_EX) === false) {
            throw new RuntimeException('Failed to save image file.');
        }

        return $relativeDir . '/' . $filename;
    }

    private static function fetchImagePaths(PDO $pdo, string $tbl_b, int $assetId): array
    {
        $stmt = $pdo->prepare("SELECT asset_image_save FROM $tbl_b WHERE asset_ID = ?");
        $stmt->execute([$assetId]);
        return array_filter(array_map(static fn($row) => $row['asset_image_save'] ?? '', $stmt->fetchAll(PDO::FETCH_ASSOC)));
    }

    private static function deleteImageFiles(array $paths): void
    {
        $base = realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . self::IMAGE_UPLOAD_DIR);
        if ($base === false) {
            return;
        }

        foreach ($paths as $path) {
            if (strpos($path, 'data:image/') === 0) {
                continue;
            }

            $fullPath = realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
            if ($fullPath !== false && strpos($fullPath, $base) === 0 && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    private static function assetImagesHasPathColumn(PDO $pdo, string $tbl_b): bool
    {
        static $cache = [];
        if (array_key_exists($tbl_b, $cache)) {
            return $cache[$tbl_b];
        }

        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM $tbl_b LIKE 'asset_image_path'");
            $cache[$tbl_b] = (bool)$stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $cache[$tbl_b] = false;
        }

        return $cache[$tbl_b];
    }
}
