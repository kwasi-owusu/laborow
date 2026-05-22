<?php
session_start();
require_once dirname(__DIR__, 2) . '/model/MDLUserActivities.php';
require_once dirname(__DIR__, 2) . '/controller/CTRLSecureLogin.php';
require_once dirname(__DIR__, 2) . '/controller/AuthEnums.php';
require_once dirname(__DIR__, 2) . '/controller/ApiAuthToken.php';
require_once dirname(__DIR__, 2) . '/controller/ApiRateLimiter.php';
require_once dirname(__DIR__, 2) . '/model/LoginUserModel.php';

final class UserLoginAPI
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = ['lgnUser', 'lgnPwd'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                self::jsonResponse(['error' => true, 'message' => "Missing field: $field"], 400);
            }
        }

        $email = filter_var(self::sanitize($data['lgnUser']), FILTER_SANITIZE_EMAIL);
        ApiRateLimiter::enforce('auth.login', $email, 8, 900);
        $password = self::sanitize($data['lgnPwd']);

        if (!$email || !$password) {
            self::jsonResponse(['error' => true, 'message' => 'Email and password are required.'], 400);
        }

        $hashed_password = hash_hmac('sha512', $password, LaborowHashKeys::password_hash->value);

        $userModel = new LoginUserModel();
        $user = $userModel->MdlShowUsers('users', '', ['em' => $email, 'ps' => $hashed_password]);

        if (!$user) {
            self::logActivity('Login Failed', [
                'actions' => 'Login Attempted',
                'status' => 'Failed',
                'usernames' => $email,
            ], 0);

            self::jsonResponse([
                'error' => true,
                'message' => 'Invalid credentials.',
                'error_code' => 112
            ]);
        }

        $user_id = (int)($user['user_id'] ?? 0);

        if (($user['userStatus'] ?? 0) != 1) {
            self::logActivity('Access Denied', [
                'actions' => 'Login Attempted',
                'status' => 'Access Denied',
                'usernames' => $email,
            ], $user_id);

            self::jsonResponse([
                'error' => true,
                'message' => 'Access Denied. Contact Admin.',
                'error_code' => 113
            ]);
        }

        $secureLogin = new CTRLSecureLogin();
        $passwordDetails = $secureLogin->is_password_valid($user_id, $hashed_password);
        $passwordDate = $passwordDetails['system_date'] ?? null;

        if ($passwordDate && self::passwordAgeInDays($passwordDate, date('Y-m-d')) >= PasswordSecurity::password_expires_after_days->value) {
            self::logActivity('Password Expired', [
                'actions' => 'Login Attempted',
                'status' => 'Password Expired',
                'usernames' => $email,
            ], $user_id);

            self::jsonResponse([
                'error' => true,
                'message' => 'Password expired. Please change your password.',
                'error_code' => 114
            ]);
        }

        $_SESSION = array_merge($_SESSION, [
            'user_id' => $user_id,
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'email' => $user['email'] ?? '',
            'user_access_level' => $user['user_access_level'] ?? '',
            'isLogin' => 1
        ]);

        $issuedToken = ApiAuthToken::issue($user);

        self::logActivity('Login Success', [
            'actions' => 'Login Attempted',
            'status' => 'Successful',
            'usernames' => $email,
        ], $user_id);

        $responseUser = self::withoutSensitiveFields([
            'user_id' => $user['user_id'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'user_phone_number' => $user['user_phone_number'] ?? '',
            'affiliate_code' => $user['affiliate_code'] ?? '',
            'user_type' => $user['user_type'] ?? '',
            'user_access_level' => $user['user_access_level'] ?? '',
            'country_code' => $user['country_code'] ?? '',
            'ip_location' => $user['ip_location'] ?? '',
            'state_region' => $user['state_region'] ?? '',
            'city' => $user['city'] ?? '',
            'is_identity_verified' => $user['is_identity_verified'] ?? '',
            'is_email_verified' => $user['is_email_verified'] ?? '',
            'is_phone_verified' => $user['is_phone_verified'] ?? '',
            'business_name' => $user['business_name'] ?? '',
            'business_phone_number' => $user['business_phone_number'] ?? '',
            'website' => $user['website'] ?? '',
            'business_email' => $user['business_email'] ?? ''
        ]);

        self::jsonResponse([
            'error' => false,
            'message' => 'Login successful.',
            'code' => 111,
            'token' => $issuedToken['token'],
            'expires_at' => $issuedToken['expires_at'],
            'user' => $responseUser
        ]);
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function withoutSensitiveFields(array $user): array
    {
        foreach (array_keys($user) as $key) {
            $normalizedKey = strtolower((string)$key);
            if (preg_match('/password|verification_code|token|secret|otp|pin|reset/', $normalizedKey)) {
                unset($user[$key]);
            }
        }

        return $user;
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private static function passwordAgeInDays(string $from, string $to): int
    {
        return abs((strtotime($to) - strtotime($from)) / 86400);
    }

    private static function logActivity(string $module, array $activity, int|string $userId): void
    {
        $model = new MDLUserActivities();
        $model->userActivitiesMDL([
            'activity_module' => $module,
            'activity_desc' => json_encode($activity),
            'user_id' => $userId
        ], 'user_activities');
    }
}

(new UserLoginAPI())->login();
