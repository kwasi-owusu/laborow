<?php

require_once dirname(__DIR__, 2) . '/model/MDLAssetStats.php';

final class AssetStatsCTRL
{
    public function incrementAssetViewCountCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $assetId = isset($data['asset_id']) ? self::sanitize($data['asset_id']) : null;

        if (empty($assetId) || !ctype_digit($assetId)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing asset ID.'], 400);
        }

        $model = new MDLAssetStats();
        $updated = $model->incrementViewCount((int) $assetId);

        if ($updated) {
            self::jsonResponse(['error' => false, 'message' => 'View count updated successfully.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Asset not found or not available.'], 404);
        }
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new AssetStatsCTRL())->incrementAssetViewCountCTRL();
