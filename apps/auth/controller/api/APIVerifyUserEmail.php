<?php

declare(strict_types=1);

date_default_timezone_set('Africa/Accra');

// Load dependencies once
require_once dirname(__DIR__, 2) . '/model/UserModel.php';
require_once dirname(__DIR__, 2) . '/controller/GetUserByEmail.php';
require_once dirname(__DIR__, 2) . '/controller/CTRLSecureLogin.php';
require_once dirname(__DIR__, 2) . '/controller/AuthEnums.php';
require_once dirname(__DIR__, 2) . '/controller/ApiRateLimiter.php';
require_once dirname(__DIR__, 2) . '/model/MDLUserActivities.php';
require_once dirname(__DIR__, 3) . '/notifications/controller/api/APISendEmail.php';
require_once dirname(__DIR__, 3) . '/template/statics/conn/anthrax.php';

final class APIVerifyAccount
{
    private string $tableUsers;
    private string $tableActivities;

    public function __construct(string $tableUsers = 'users', string $tableActivities = 'user_activities')
    {
        $this->tableUsers      = $tableUsers;
        $this->tableActivities = $tableActivities;
    }

    private function ident(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new InvalidArgumentException("Invalid table or column identifier.");
        }
        return "`$name`";
    }

    private function json(int $status, array $body): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($body, JSON_UNESCAPED_SLASHES);
        exit;
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

    private function logActivity(string $module, array $activity, int|string $userId): void
    {
        try {
            $logger = new MDLUserActivities();
            $logger->userActivitiesMDL([
                'activity_module' => $module,
                'activity_desc'   => json_encode($activity, JSON_UNESCAPED_SLASHES),
                'user_id'         => $userId
            ], $this->tableActivities);
        } catch (\Throwable $e) {
            error_log('[APIVerifyAccount] Activity log failed: ' . $e->getMessage());
        }
    }

    public function verify_this_user_account(): void
    {
        $this->cors();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(405, ['error' => true, 'message' => 'Method not allowed.']);
        }

        $input = file_get_contents('php://input');
        $payload = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            $this->json(400, ['error' => true, 'message' => 'Invalid JSON payload.']);
        }

        if (!isset($payload['verification_code'])) {
            $this->json(400, ['error' => true, 'message' => 'Invalid request.']);
        }

        $userId = isset($payload['user_id']) ? trim((string)$payload['user_id']) : '';
        $code   = trim((string)$payload['verification_code']);
        ApiRateLimiter::enforce('auth.verify-email', $userId !== '' ? $userId : $code, 10, 900);

        if (($userId !== '' && !ctype_digit($userId)) || !preg_match('/^\d{6}$/', $code)) {
            $this->json(422, ['error' => true, 'message' => 'Invalid input.']);
        }
        try {
            $pdo       = (new Connection())->Connect();
            $tblUsers  = $this->ident($this->tableUsers);

            // Get user. Current mobile clients send only verification_code, so keep that legacy path.
            if ($userId !== '') {
                $stmt = $pdo->prepare("SELECT user_id, email, userStatus, verification_code FROM $tblUsers WHERE user_id = :id LIMIT 1");
                $stmt->execute([':id' => (int)$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $pdo->prepare("SELECT user_id, email, userStatus, verification_code FROM $tblUsers WHERE verification_code = :code LIMIT 2");
                $stmt->execute([':code' => $code]);
                $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($matches) > 1) {
                    $this->json(409, ['error' => true, 'message' => 'Verification code matched multiple users. Please request a new code.']);
                }
                $user = $matches[0] ?? false;
                if ($user) {
                    $userId = (string)$user['user_id'];
                }
            }

            if (!$user) {
                $this->json(404, ['error' => true, 'message' => 'User not found.']);
            }

            $status = (int)($user['userStatus'] ?? 0);
            $storedCode = $user['verification_code'] ?? '';
            $email = $user['email'] ?? '';

            if ($status === 1 && $storedCode === '') {
                $this->json(409, ['error' => true, 'message' => 'Account already verified.']);
            }

            if ($storedCode !== $code) {
                $this->logActivity('User Verification', [
                    'action' => 'Failed Verification',
                    'reason' => 'Code Mismatch',
                    'user_id' => $userId,
                ], $userId);

                $this->json(401, ['error' => true, 'message' => 'Invalid verification code.']);
            }

            // Update user status
            $pdo->beginTransaction();
            $update = $pdo->prepare("UPDATE $tblUsers SET is_email_verified = 'yes', userStatus = 1, verified_at = NOW(), verification_code = NULL WHERE user_id = :id AND verification_code = :code");
            $update->execute([':id' => (int)$userId, ':code' => $code]);

            if ($update->rowCount() < 1) {
                $pdo->rollBack();
                $this->json(409, ['error' => true, 'message' => 'Account could not be verified. Please request a new code.']);
            }

            $pdo->commit();

            $this->logActivity('User Verification', [
                'action' => 'Account Verified',
                'user_id' => $userId,
                'email' => $email,
            ], $userId);

            $this->json(200, [
                'error' => false,
                'message' => 'Account verified successfully.',
                'data' => [
                    'user_id' => (int)$userId,
                    'email'   => $email
                ]
            ]);

        } catch (\Throwable $e) {
            error_log('[APIVerifyAccount] ' . $e->getMessage());
            $this->json(500, ['error' => true, 'message' => 'Server error.']);
        }
    }
}

// Execute
(new APIVerifyAccount())->verify_this_user_account();
