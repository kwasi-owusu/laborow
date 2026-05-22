<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchAllCTRL
{
    public function selectSingleAssetCTRL(): void
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

        $model = new MDLFetchListing();
        $result = $model->select_single_asset((int)$assetId);

        if ($result) {
            self::jsonResponse(['error' => false, 'data' => $result]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Asset not found.'], 404);
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

(new FetchAllCTRL())->selectSingleAssetCTRL();
