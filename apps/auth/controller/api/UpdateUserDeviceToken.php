<?php
session_start();
ob_start();

date_default_timezone_set('Africa/Accra');

require_once dirname(__DIR__, 2) . '/model/UserModel.php';
require_once dirname(__DIR__, 2) . '/controller/ApiAuthToken.php';

class UpdateUserDeviceToken
{
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function update_this_user_device_token(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'));

        if (!is_object($input)) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        function sanitize($val)
        {
            return htmlspecialchars(strip_tags(trim($val ?? '')), ENT_QUOTES, 'UTF-8');
        }

        $user_id      = sanitize($input->user_id ?? '');
        $device_token = sanitize($input->device_token ?? '');

        if (empty($user_id) || !ctype_digit((string)$user_id)) {
            $this->jsonResponse(['error' => true, 'message' => 'User ID is required and must be numeric.'], 400);
        }

        // Compatibility bridge: old mobile clients do not send a bearer token yet.
        // If a token is present, enforce it; if absent, keep legacy behavior temporarily.
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken !== null) {
            $authUser = ApiAuthToken::validate($bearerToken);
            if (!$authUser) {
                $this->jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
            }

            if ((int)$authUser['user_id'] !== (int)$user_id) {
                $this->jsonResponse(['error' => true, 'message' => 'Token user does not match target user.'], 403);
            }
        }

        if (empty($device_token)) {
            $this->jsonResponse(['error' => true, 'message' => 'Device Token is required.'], 400);
        }

        $data = [
            'uid'   => $user_id,
            'd_tkn' => $device_token
        ];

        $success = (new UserModel())->update_device_token($data);

        if ($success) {
            $this->jsonResponse(['error' => false, 'message' => 'Device token updated successfully.']);
        } else {
            $this->jsonResponse(['error' => true, 'message' => 'Failed to update device token.'], 500);
        }
    }
}

(new UpdateUserDeviceToken())->update_this_user_device_token();