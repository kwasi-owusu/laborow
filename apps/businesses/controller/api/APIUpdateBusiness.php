<?php
require_once dirname(__DIR__, 2) . '/model/BusinessesMDL.php';
require_once dirname(__DIR__, 3) . '/auth/model/MDLUserActivities.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

class APIUpdateBusiness
{
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        if (!isset($input['business_id']) || !is_numeric($input['business_id'])) {
            self::jsonResponse(['error' => true, 'message' => 'business_id is required and must be numeric.'], 400);
        }

        $businessId = (int)$input['business_id'];
        $model = new BusinessesMDL();

        // Compatibility bridge: old mobile clients do not send a bearer token yet.
        // If a token is present, enforce ownership; if absent, keep legacy behavior temporarily.
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken !== null) {
            $authUser = ApiAuthToken::validate($bearerToken);
            if (!$authUser) {
                self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
            }

            $ownerId = $model->getBusinessOwnerId($businessId);
            if ($ownerId === null) {
                self::jsonResponse(['error' => true, 'message' => 'Business not found.'], 404);
            }

            if ((int)$authUser['user_id'] !== $ownerId) {
                self::jsonResponse(['error' => true, 'message' => 'Token user does not own this business.'], 403);
            }
        }

        // Sanitize values
        function sanitize($val) {
            return htmlspecialchars(strip_tags(trim((string)$val)), ENT_QUOTES, 'UTF-8');
        }

        $data = [
            'business_name'          => sanitize($input['business_name'] ?? ''),
            'business_phone_number' => sanitize($input['business_phone_number'] ?? ''),
            'website'               => sanitize($input['website'] ?? ''),
            'business_email'        => sanitize($input['business_email'] ?? ''),
            'business_profile'      => sanitize($input['business_profile'] ?? ''),
            'business_address'      => sanitize($input['business_address'] ?? ''),
            'business_state'        => sanitize($input['business_state'] ?? ''),
            'business_city'         => sanitize($input['business_city'] ?? ''),
            'business_status'       => sanitize($input['business_status'] ?? 'active'),
        ];

        $updated = $model->updateRentalBusiness($businessId, $data);

        if (!$updated) {
            self::logActivity('Update Business', [
                'actions' => 'Update Rental Business',
                'status'  => 'Failed',
                'business_id' => $businessId
            ], 0);

            self::jsonResponse([
                'error' => true,
                'message' => 'Update failed or no changes made.',
                'code' => 500
            ]);
        }

        self::logActivity('Update Business', [
            'actions' => 'Updated Rental Business',
            'status'  => 'Success',
            'business_id' => $businessId
        ], 0);

        self::jsonResponse([
            'error' => false,
            'message' => 'Business updated successfully.',
            'code' => 200
        ]);
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private static function logActivity(string $module, array $activity, int|string $user_id): void
    {
        $mdl = new MDLUserActivities();
        $mdl->userActivitiesMDL([
            'activity_module' => $module,
            'activity_desc'   => json_encode($activity),
            'user_id'         => $user_id
        ], 'user_activities');
    }
}

(new APIUpdateBusiness())->update();