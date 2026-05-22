<?php

require_once dirname(__DIR__, 2) . '/model/UserModel.php';
require_once dirname(__DIR__, 2) . '/controller/AuthEnums.php';
require_once dirname(__DIR__, 2) . '/controller/ApiAuthToken.php';
require_once dirname(__DIR__, 2) . '/controller/ApiRateLimiter.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

final class UpdateMyPasswordController
{
    private const USER_TABLE = 'users';

    public function changeMyPassword(): void
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

        $user_ID      = isset($dt->user_ID) ? (int) sanitizeInput($dt->user_ID) : 0;
        $user_pwd     = isset($dt->c_password) ? sanitizeInput($dt->c_password) : '';
        $phone_number = isset($dt->phone_number) ? sanitizeInput($dt->phone_number) : '';
        $updatedBy    = $user_ID ?: null;
        ApiRateLimiter::enforce('auth.update-password', (string)$user_ID, 5, 3600);

        if (empty($user_pwd)) {
            self::jsonResponse(['error' => true, 'message' => 'User password cannot be empty.'], 400);
        }

        if (empty($phone_number)) {
            self::jsonResponse(['error' => true, 'message' => 'Phone number cannot be empty.'], 400);
        }

        if (empty($updatedBy)) {
            self::jsonResponse(['error' => true, 'message' => 'Session expired or user not authenticated.'], 401);
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

        $password_hash_key = LaborowHashKeys::password_hash->value;
        $hashed_password = hash_hmac('sha512', $user_pwd, $password_hash_key);

        $data = [
            'ud'  => $user_ID,
            'phn' => $phone_number,
            'npd' => $hashed_password,
            'lbd' => $updatedBy,
            'lbn' => date('Y-m-d'),
        ];

        if (UserModel::updateUserPwd(self::USER_TABLE, $data)) {
            self::jsonResponse(['error' => false, 'message' => 'Password updated successfully.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Password update failed. Please try again.'], 500);
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

(new UpdateMyPasswordController())->changeMyPassword();