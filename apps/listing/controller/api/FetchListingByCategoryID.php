<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchListingByCategoryID
{
    public function fetchListingByCategoryID(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $category_id = self::firstValue($data, ['category_id', 'category', 'asset_category_id']);

        if (empty($category_id)) {
            self::jsonResponse(['error' => true, 'message' => 'Category id is required.'], 400);
        }

        $model = new MDLFetchListing();
        $results = $model->select_asset_by_category($category_id);

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No listings found for this sub-category.'], 404);
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

(new FetchListingByCategoryID())->fetchListingByCategoryID();
