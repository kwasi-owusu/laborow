<?php

require_once '../model/MDLFetchListing.php';

final class FetchAllCTRL extends MDLFetchListing
{
    private string $tbl;
    private string $tbl_b;
    private string $tbl_c;
    private string $tbl_d;

    public function __construct(
        string $tbl = 'assets',
        string $tbl_b = 'asset_images',
        string $tbl_c = 'asset_category',
        string $tbl_d = 'asset_sub_category'
    ) {
        $this->tbl   = $tbl;
        $this->tbl_b = $tbl_b;
        $this->tbl_c = $tbl_c;
        $this->tbl_d = $tbl_d;
    }

    public function fetchListingByCategoryCTRL(): void
    {
        // Only accept POST for security and cleaner body handling
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $asset_category_ID = isset($_POST['asset_category_id']) ? trim(strip_tags($_POST['asset_category_id'])) : '';

        if (empty($asset_category_ID) || $asset_category_ID === '_9') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing asset category ID.'], 400);
        }

        $mdl = new MDLFetchListing();
        $results = $mdl->fetchListingByCategoryMDL($this->tbl, $this->tbl_c, $asset_category_ID);

        if (!empty($results) && is_array($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No listings found for this category.'], 404);
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

$controller = new FetchAllCTRL();
$controller->fetchListingByCategoryCTRL();