<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Accra');

require_once dirname(__DIR__, 2) . '/controller/ApiRateLimiter.php';
require_once dirname(__DIR__, 3) . '/template/statics/conn/anthrax.php';
require_once dirname(__DIR__, 3) . '/notifications/controller/api/APISendEmail.php';

final class ResendVerificationCodeAPI
{
    private const GENERIC_MESSAGE = 'If your account exists and still needs verification, a new code will be sent.';

    public function handle(): void
    {
        $this->cors();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['error' => true, 'message' => 'Method not allowed.']);
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            $this->json(400, ['error' => true, 'message' => 'Invalid JSON payload.']);
        }

        $email = filter_var(self::clean($payload['email'] ?? $payload['lgnUser'] ?? ''), FILTER_SANITIZE_EMAIL);
        ApiRateLimiter::enforce('auth.resend-verification', $email, 4, 3600);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(400, ['error' => true, 'message' => 'A valid email is required.']);
        }

        try {
            $pdo = (new Connection())->Connect();
            $stmt = $pdo->prepare("SELECT user_id, first_name, email, userStatus, is_email_verified FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || self::isVerified($user)) {
                $this->json(200, ['error' => false, 'message' => self::GENERIC_MESSAGE]);
            }

            $code = random_int(100000, 999999);
            $update = $pdo->prepare('UPDATE users SET verification_code = :code, last_update_on = NOW() WHERE user_id = :id AND COALESCE(is_email_verified, \'no\') <> \'yes\' AND COALESCE(userStatus, 0) <> 1');
            $update->execute([
                ':code' => $code,
                ':id' => (int)$user['user_id']
            ]);

            if ($update->rowCount() < 1) {
                $this->json(200, ['error' => false, 'message' => self::GENERIC_MESSAGE]);
            }

            $sent = $this->sendVerificationEmail($user, $code);
            if (!$sent) {
                error_log('[ResendVerificationCode] Verification email was not sent for email hash: ' . hash('sha256', $email));
            }

            $this->json(200, ['error' => false, 'message' => self::GENERIC_MESSAGE]);
        } catch (Throwable $e) {
            error_log('[ResendVerificationCode] Server error while resending verification code.');
            $this->json(500, ['error' => true, 'message' => 'Server error.']);
        }
    }

    private function sendVerificationEmail(array $user, int $code): bool
    {
        try {
            $firstName = (string)($user['first_name'] ?? '');
            return (new APISendEmail())->send_email([
                'recipient' => (string)$user['email'],
                'recipient_name' => $firstName,
                'subject' => 'Your Threnz Verification Code',
                'mail_body' => self::verificationHtml($firstName, $code),
                'mail_body_alt' => "Your Threnz verification code is: {$code}\n\nIf you did not request this, you can ignore this email.",
            ]);
        } catch (Throwable $e) {
            error_log('[ResendVerificationCode] Verification email send failed.');
            return false;
        }
    }

    private static function verificationHtml(string $firstName, int $code): string
    {
        $safeFirstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');

        return "
            <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;\">
                <h2 style=\"color: #1a73e8;\">Verify Your <strong>Threnz</strong> Account</h2>
                <p>Hi {$safeFirstName},</p>
                <p>Use the code below to verify your email address:</p>
                <p style=\"font-size: 20px; font-weight: bold; color: #1a73e8; background-color: #f1f3f4; padding: 10px; border-radius: 6px; text-align: center;\">{$code}</p>
                <p>If you did not request this code, you can safely ignore this email.</p>
                <p>Kind regards,<br>The Threnz Team</p>
            </div>
        ";
    }

    private static function isVerified(array $user): bool
    {
        return (int)($user['userStatus'] ?? 0) === 1 || strtolower((string)($user['is_email_verified'] ?? '')) === 'yes';
    }

    private static function clean($value): string
    {
        return htmlspecialchars(strip_tags(trim((string)$value)), ENT_QUOTES, 'UTF-8');
    }

    private function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 3600');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    private function json(int $status, array $body): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($body, JSON_UNESCAPED_SLASHES);
        exit;
    }
}

(new ResendVerificationCodeAPI())->handle();
