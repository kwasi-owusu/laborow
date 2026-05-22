<?php

require_once dirname(__DIR__, 2) . '/model/MDLAssetCategory.php';

final class GetAssetCategoryList
{
    public function fetchCategories(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        // $input = file_get_contents('php://input');
        // $data = json_decode($input, true);
        

        $model = new MDLAssetCategory();
        $results = $model->get_assets_categoriesMDL();

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

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new GetAssetCategoryList())->fetchCategories();
