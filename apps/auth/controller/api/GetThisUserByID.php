<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/model/GetThisUser.php';
require_once dirname(__DIR__) . '/ApiAuthToken.php';

final class GetThisUserAPI
{
    public function fetchThisUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $dt = json_decode(@file_get_contents('php://input'));

        if (!is_object($dt)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON input.'], 400);
        }

        $user_ID = isset($dt->user_ID) ? (int) self::sanitizeInput((string)$dt->user_ID) : 0;

        if ($user_ID <= 0) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or missing user ID.'], 400);
        }

        self::enforceUserIfTokenPresent($user_ID);

        $user = GetThisUser::selectThisUser($user_ID);

        if (!empty($user) && is_array($user)) {
            self::jsonResponse(['error' => false, 'data' => $user]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'User not found.'], 404);
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

    private static function sanitizeInput(string $dta): string
    {
        return htmlspecialchars(strip_tags(trim($dta)), ENT_QUOTES, 'UTF-8');
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new GetThisUserAPI())->fetchThisUser();
