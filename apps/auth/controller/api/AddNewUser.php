<?php
session_start();
ob_start();

date_default_timezone_set('Africa/Accra');

require_once dirname(__DIR__, 2) . '/model/UserModel.php';
require_once dirname(__DIR__, 2) . '/controller/GetUserByEmail.php';
require_once dirname(__DIR__, 2) . '/model/MDLUserActivities.php';
require_once dirname(__DIR__, 2) . '/controller/CTRLSecureLogin.php';
require_once dirname(__DIR__, 2) . '/controller/AuthEnums.php';
require_once dirname(__DIR__, 2) . '/controller/ApiRateLimiter.php';
require_once dirname(__DIR__, 3) . '/notifications/controller/api/APISendEmail.php';

class AddUserController
{
    private string $table_a;
    private string $table_b;

    public function __construct(string $table_a, string $table_b)
    {
        $this->table_a = $table_a;
        $this->table_b = $table_b;
    }

    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function logActivity(string $module, array $activity, int|string $user_id): void
    {
        $logger = new MDLUserActivities();

        $activity_data = [
            'activity_module' => $module,
            'activity_desc' => json_encode($activity),
            'user_id' => $user_id,
        ];

        $logger->userActivitiesMDL($activity_data, $this->table_b);
    }

    public function addUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid request method.'], 405);
        }

        $dt = json_decode(@file_get_contents('php://input'));
        if (json_last_error() !== JSON_ERROR_NONE || !is_object($dt)) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $firstName    = self::sanitizeInput($dt->fname ?? '');
        $lastName     = self::sanitizeInput($dt->lname ?? '');
        $user_email   = filter_var(self::sanitizeInput($dt->lgnUser ?? ''), FILTER_SANITIZE_EMAIL);
        ApiRateLimiter::enforce('auth.signup', $user_email, 5, 3600);
        $country      = self::sanitizeInput($dt->country ?? '');
        $state_region = self::sanitizeInput($dt->state_region ?? '');
        $city_town    = self::sanitizeInput($dt->city_town ?? '');
        $user_pwd     = self::sanitizeInput($dt->lgnPwd ?? '');
        $phone_number = self::sanitizeInput($dt->phone_number ?? '');
        $user_type    = self::sanitizeInput($dt->user_type ?? 'individual');

        $business_name = '';
        $business_phone_number = '';
        $website = null;
        $business_email = '';
        $business_address = '';
        $business_state = '';
        $business_city = '';

        if ($user_type !== 'individual') {
            $business_name = self::sanitizeInput($dt->business_name ?? '');
            $business_phone_number = self::sanitizeInput($dt->business_phone_number ?? '');
            $website = self::sanitizeInput($dt->website ?? '');
            $business_email = filter_var(self::sanitizeInput($dt->business_email ?? ''), FILTER_SANITIZE_EMAIL);
            $business_address = self::sanitizeInput($dt->business_address ?? '');
            $business_state = self::sanitizeInput($dt->business_state ?? '');
            $business_city = self::sanitizeInput($dt->business_city ?? '');
        }

        $user_role = 2;

        if ($firstName === '') {
            $this->jsonResponse(['error' => true, 'message' => 'First Name cannot be empty.'], 400);
        }
        if ($lastName === '') {
            $this->jsonResponse(['error' => true, 'message' => 'Last Name cannot be empty.'], 400);
        }
        if ($user_email === '' || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => true, 'message' => 'A valid email is required.'], 400);
        }
        if ($user_pwd === '') {
            $this->jsonResponse(['error' => true, 'message' => 'Password cannot be empty.'], 400);
        }

        if ($user_type !== 'individual') {
            foreach ([
                'Business name' => $business_name,
                'Business phone number' => $business_phone_number,
                'Business email' => $business_email,
                'Business address' => $business_address,
                'Business state' => $business_state,
                'Business city' => $business_city,
            ] as $field => $value) {
                if ($value === '') {
                    $this->jsonResponse(['error' => true, 'message' => $field . ' cannot be empty.'], 400);
                }
            }

            if (!filter_var($business_email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(['error' => true, 'message' => 'A valid business email is required.'], 400);
            }
        }

        $secureLogin = new CTRLSecureLogin();

        if ($secureLogin->is_email_already_exists($user_email) > 0) {
            $this->jsonResponse(['error' => true, 'message' => 'User already exists.'], 409);
        }

        $password_hash_key = LaborowHashKeys::password_hash->value;
        $hashed_password = hash_hmac('sha512', $user_pwd, $password_hash_key);
        $userKey = hash_hmac('sha512', "{$user_email}-{$phone_number}-{$user_pwd}", $user_email);
        $verification_code = random_int(100000, 999999);

        $data = [
            'fn'  => $firstName,
            'ln'  => $lastName,
            'em'  => $user_email,
            'ctr' => $country,
            'stt' => $state_region,
            'cty' => $city_town,
            'pd'  => $hashed_password,
            'rl'  => $user_role,
            'phn' => $phone_number,
            'ust' => $user_type,
            'usk' => $userKey,
            'verification_code' => $verification_code,
            'business_name' => $business_name,
            'business_phone_number' => $business_phone_number,
            'website' => $website,
            'business_email' => $business_email,
            'business_address' => $business_address,
            'business_state' => $business_state,
            'business_city' => $business_city
        ];

        if (UserModel::addUser($this->table_a, $this->table_b, $data)) {
            $this->logActivity('User Registration', [
                'actions' => 'Registration',
                'status'  => 'Successful',
                'usernames' => $user_email
            ], 0);

            $emailSent = $this->sendVerificationEmail($data, $verification_code);
            $message = 'Entry successful. Please check your email for a confirmation code.';
            if (!$emailSent) {
                error_log('[APISaveNewUser] Verification email was not sent for user email hash: ' . hash('sha256', $user_email));
                $message = 'Entry successful, but we could not send the verification email right now.';
            }

            $this->jsonResponse([
                'error' => false,
                'message' => $message,
                'code' => 200
            ]);
        } else {
            $this->jsonResponse(['error' => true, 'message' => 'Entry unsuccessful. Please try again.'], 500);
        }
    }

    private function sendVerificationEmail(array $data, int $verificationCode): bool
    {
        try {
            return (new APISendEmail())->send_email([
                'recipient'      => $data['em'],
                'recipient_name' => $data['fn'] ?? '',
                'subject'        => 'Verify Your Threnz Account',
                'mail_body'      => self::verificationHtml($data['fn'] ?? '', $verificationCode),
                'mail_body_alt'  => "Welcome to Threnz!\n\nYour verification code is: {$verificationCode}\n\nUse this code to verify your email.\n\nIf you didn't register, please ignore this email.",
            ]);
        } catch (Throwable $e) {
            error_log('[APISaveNewUser] Verification email send failed.');
            return false;
        }
    }

    private static function verificationHtml(string $firstName, int $verificationCode): string
    {
        $safeFirstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');

        return "
            <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;\">
                <h2 style=\"color: #1a73e8;\">Welcome to <strong>Threnz</strong>!</h2>
                <p>Hi {$safeFirstName},</p>
                <p>Thank you for joining <strong>Threnz</strong>, your smart and secure way to rent anything, anytime.</p>
                <p style=\"font-size: 16px;\">To verify your email, please use the code below:</p>
                <p style=\"font-size: 20px; font-weight: bold; color: #1a73e8; background-color: #f1f3f4; padding: 10px; border-radius: 6px; text-align: center;\">{$verificationCode}</p>
                <p>This code is valid for a limited time. Please do not share it with anyone.</p>
                <p style=\"margin-top: 30px;\">If you did not create an account with us, you can safely ignore this email.</p>
                <p>Kind regards,<br>The Threnz Team</p>
                <hr style=\"margin-top: 40px;\">
                <p style=\"font-size: 12px; color: #888;\">Need help? Contact support at support@threnz.com</p>
            </div>
        ";
    }

    private static function sanitizeInput($dta): string
    {
        return htmlspecialchars(strip_tags(trim((string)$dta)), ENT_QUOTES, 'UTF-8');
    }
}

(new AddUserController('users', 'user_activities'))->addUser();
