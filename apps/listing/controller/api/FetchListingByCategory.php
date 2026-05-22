<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchAllCTRL
{
    public function fetchListingByCategoryCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $categoryId = self::firstValue($data, ['asset_category_id', 'category_id', 'category']);

        if (empty($categoryId) || $categoryId === '_9') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing asset category.'], 400);
        }

        $model = new MDLFetchListing();
        $results = $model->fetchListingByCategoryMDL($categoryId);

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No listings found for this category.'], 404);
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

(new FetchAllCTRL())->fetchListingByCategoryCTRL();
