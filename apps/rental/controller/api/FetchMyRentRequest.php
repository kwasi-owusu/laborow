<?php

require_once dirname(__DIR__, 2) . '/model/FetchMyRentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

class FetchMyRentRequest
{
    public function fetchMyRentalRequestCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $user_ID = isset($data['user_ID']) ? self::sanitize((string)$data['user_ID']) : null;

        if (empty($user_ID) || $user_ID === '_9' || !ctype_digit($user_ID)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing User ID.'], 400);
        }

        self::enforceUserIfTokenPresent((int)$user_ID);

        $model = new FetchMyRentals();
        $results = $model->fetchMyRentRequest((int) $user_ID);

        if (!empty($results)) {
            self::jsonResponse(['error' => false, 'data' => $results]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'No rental request found for this user.'], 404);
        }
    }

    private static function enforceUserIfTokenPresent(int $userId): void
    {
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken === null) {
            return;
        }

        $authUser = ApiAuthToken::validate($bearerToken);
        if (!$authUser) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
        }

        if ((int)$authUser['user_id'] !== $userId) {
            self::jsonResponse(['error' => true, 'message' => 'Token user does not match requested user.'], 403);
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

(new FetchMyRentRequest())->fetchMyRentalRequestCTRL();