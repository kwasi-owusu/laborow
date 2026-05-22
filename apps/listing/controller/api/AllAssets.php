<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchAllCTRL
{
    public function selectAllAssetsCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use GET.'], 405);
        }

        // Capture filters from GET request
        $filters = [
            'keyword'        => $_GET['q'] ?? '',
            'category_ID'    => $_GET['category'] ?? $_GET['category_id'] ?? $_GET['asset_category_id'] ?? null,
            'sub_category_ID'=> $_GET['sub_category'] ?? $_GET['sub_category_id'] ?? $_GET['asset_sub_category_id'] ?? null,
            'min_price'      => $_GET['min_price'] ?? null,
            'max_price'      => $_GET['max_price'] ?? null,
            'rented_by'      => $_GET['rented_by'] ?? null,
            'lat'            => $_GET['lat'] ?? null,
            'lng'            => $_GET['lng'] ?? null,
            'radius'         => $_GET['radius'] ?? null
        ];

        // Handle pagination
        $page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Fetch filtered and paginated assets
        $model = new MDLFetchListing();
        $results = $model->fetchFilteredAssets($filters, $limit, $offset);

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No assets found.'], 404);
        }
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Run the controller
(new FetchAllCTRL())->selectAllAssetsCTRL();
