<?php

require_once dirname(__DIR__, 2) . '/model/MDLFetchListing.php';

final class FetchAllCTRL
{
    public function selectMostViewedAssetsCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use GET.'], 405);
        }

        $model = new MDLFetchListing();
        $results = $model->select_most_viewed();

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No most viewed assets found.'], 404);
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

(new FetchAllCTRL())->selectMostViewedAssetsCTRL();
