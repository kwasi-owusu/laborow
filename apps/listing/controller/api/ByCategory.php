<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchAllCTRL
{
    public function selectAssetByCategoryCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $categoryId = self::firstValue($data, ['category_id', 'category', 'asset_category_id']);

        if (empty($categoryId)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing category.'], 400);
        }

        $model = new MDLFetchListing();
        $results = $model->select_asset_by_category($categoryId);

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No assets found under this category.'], 404);
        }
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function firstValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                return self::sanitize((string)$data[$key]);
            }
        }
        return null;
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new FetchAllCTRL())->selectAssetByCategoryCTRL();
