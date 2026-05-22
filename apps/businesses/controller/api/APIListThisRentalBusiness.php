<?php
require_once dirname(__DIR__, 2) . '/model/BusinessesMDL.php';
require_once dirname(__DIR__, 3) . '/auth/model/MDLUserActivities.php';

class APIListThisRentalBusiness
{
    public function get(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use GET.'], 405);
        }

        if (!isset($_GET['business_id']) || !is_numeric($_GET['business_id'])) {
            self::jsonResponse(['error' => true, 'message' => 'business_id is required and must be numeric.'], 400);
        }

        $businessId = (int)$_GET['business_id'];

        $model = new BusinessesMDL();
        $business = $model->getSingleRentalBusiness($businessId);

        if (!$business) {
            self::logActivity('Business Fetch Failed', [
                'actions' => 'Fetch Rental Business by ID',
                'status' => 'Not Found',
                'business_id' => $businessId
            ], 0);

            self::jsonResponse([
                'error' => true,
                'message' => 'Rental business not found.',
                'code' => 404
            ]);
        }

        self::jsonResponse([
            'error' => false,
            'message' => 'Rental business found.',
            'code' => 200,
            'business' => $business
        ]);
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
(new APIListThisRentalBusiness())->get();
