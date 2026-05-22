<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/model/UserModel.php';
require_once dirname(__DIR__, 2) . '/controller/ApiAuthToken.php';

final class UpdateUserController
{
    private const USER_TABLE = 'users';

    public function updateUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $dt = json_decode(@file_get_contents('php://input'));
        if (!is_object($dt)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        function sanitizeInput($dta)
        {
            return htmlspecialchars(strip_tags(trim($dta)), ENT_QUOTES, 'UTF-8');
        }

        $firstName    = isset($dt->fname) ? sanitizeInput($dt->fname) : '';
        $lastName     = isset($dt->lname) ? sanitizeInput($dt->lname) : '';
        $user_email   = isset($dt->user_email) ? sanitizeInput($dt->user_email) : '';
        $user_role    = isset($dt->rle) ? sanitizeInput($dt->rle) : '';
        $user_ID      = isset($dt->user_ID) ? (int) sanitizeInput($dt->user_ID) : 0;
        $phone_number = isset($dt->phone_number) ? sanitizeInput($dt->phone_number) : '';
        $updatedBy    = $user_ID ?: null;

        // Validate required fields
        if (empty($firstName)) {
            self::jsonResponse(['error' => true, 'message' => 'First Name cannot be empty.'], 400);
        }
        if (empty($lastName)) {
            self::jsonResponse(['error' => true, 'message' => 'Last Name cannot be empty.'], 400);
        }
        if (empty($user_email)) {
            self::jsonResponse(['error' => true, 'message' => 'Email cannot be empty.'], 400);
        }
        if (empty($user_role)) {
            self::jsonResponse(['error' => true, 'message' => 'User role cannot be empty.'], 400);
        }
        if (empty($updatedBy)) {
            self::jsonResponse(['error' => true, 'message' => 'Unauthorized. Session user ID missing.'], 401);
        }

        // Compatibility bridge: old mobile clients do not send a bearer token yet.
        // If a token is present, enforce it; if absent, keep legacy behavior temporarily.
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken !== null) {
            $authUser = ApiAuthToken::validate($bearerToken);
            if (!$authUser) {
                self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
            }

            if ((int)$authUser['user_id'] !== (int)$user_ID) {
                self::jsonResponse(['error' => true, 'message' => 'Token user does not match target user.'], 403);
            }
        }

        // Prepare data
        $last_update_on = date('Y-m-d');

        $data = [
            'lbd' => $updatedBy,
            'fn'  => $firstName,
            'ln'  => $lastName,
            'em'  => $user_email,
            'rl'  => $user_role,
            'ud'  => $user_ID,
            'nn'  => $last_update_on,
            'phn' => $phone_number
        ];

        if (UserModel::editUserDetails(self::USER_TABLE, $data)) {
            self::jsonResponse(['error' => false, 'message' => 'Update successful.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Update failed. Please try again.'], 500);
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

(new UpdateUserController())->updateUser();