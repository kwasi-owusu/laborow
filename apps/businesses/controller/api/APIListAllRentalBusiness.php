<?php
session_start();
require_once dirname(__DIR__, 3) . '/auth/model/MDLUserActivities.php';
require_once dirname(__DIR__, 2) . '/model/BusinessesMDL.php';

final class AllRentalBusinessesAPI
{
    public function listBusinesses(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use GET.'], 405);
        }

        $page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        $model = new BusinessesMDL();
        $results = $model->AllRentalBusinesses([
            'offset' => $offset,
            'limit' => $limit
        ]);

        if (!$results || count($results) === 0) {
            self::logActivity('Load Businesses', [
                'actions' => 'List Rental Businesses',
                'status' => 'No Records Found',
                'usernames' => '',
            ], 0);

            self::jsonResponse([
                'error' => true,
                'message' => 'No businesses found.',
                'code' => 204
            ]);
        }

        self::jsonResponse([
            'error' => false,
            'message' => 'Data Load Successful',
            'code' => 200,
            'page' => $page,
            'limit' => $limit,
            'businesses' => $results
        ]);
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

    private static function logActivity(string $module, array $activity, int|string $qryId): void
    {
        $model = new MDLUserActivities();
        $model->userActivitiesMDL([
            'activity_module' => $module,
            'activity_desc' => json_encode($activity),
            'user_id' => $qryId
        ], 'user_activities');
    }
}

(new AllRentalBusinessesAPI())->listBusinesses();