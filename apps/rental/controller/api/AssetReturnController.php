<?php
session_start();
ob_start();
date_default_timezone_set('Africa/Accra');

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';
require_once dirname(__DIR__, 3) . '/notifications/controller/api/SendAppNotificationWithFCM.php';

class MarkAssetAsReturned
{
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function clean($val): string
    {
        return htmlspecialchars(strip_tags(trim($val ?? '')), ENT_QUOTES, 'UTF-8');
    }

    private function enforceRenterIfTokenPresent(Rentals $model, int $rentId): void
    {
        $bearer = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearer === null) {
            return;
        }

        $auth = ApiAuthToken::validate($bearer);
        if (!$auth) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
        }

        $rent = $model->get_rent_participants($rentId);
        if (!$rent) {
            $this->jsonResponse(['error' => true, 'message' => 'Rental not found.'], 404);
        }

        if ((int) $rent['user_ID'] !== (int) $auth['user_id']) {
            $this->jsonResponse(['error' => true, 'message' => 'You are not allowed to return this rental.'], 403);
        }
    }

    private function logFcmDispatch(int $rentId, bool $hasDeviceToken): void
    {
        file_put_contents(__DIR__ . '/fcm.log', json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'rent_id' => $rentId,
            'device_token_present' => $hasDeviceToken
        ], JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'));
        if (!is_object($input)) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $rentId = $this->clean($input->current_rent_ID ?? '');
        $returnPhoto = $this->clean($input->return_confirmation_photo ?? '');
        $returnNotes = $this->clean($input->return_notes ?? '');
        $actualReturnDate = $this->clean($input->actual_return_date ?? '');

        if ($rentId === '' || !ctype_digit((string) $rentId) || $returnPhoto === '') {
            $this->jsonResponse(['error' => true, 'message' => 'Missing rent ID or return photo.'], 400);
        }

        $model = new Rentals();
        $this->enforceRenterIfTokenPresent($model, (int) $rentId);
        $result = $model->mark_asset_as_returned($rentId, $actualReturnDate, $returnNotes, $returnPhoto);
        $token = $model->get_owner_device_token($rentId);

        $deviceToken = $token['current_device_token'] ?? '';
        $this->logFcmDispatch((int) $rentId, $deviceToken !== '');

        if ($result['status'] == true) {
            if (!empty($deviceToken)) {
                $title = 'Confirm Asset Return';
                $body = 'Has your asset "' . ($result['asset_title'] ?? 'this asset') . '" been returned by the borrower?';
                (new FCMNotification())->send($deviceToken, $title, $body);
            }

            $this->jsonResponse(['error' => false, 'message' => 'Asset return marked. Awaiting owner confirmation.']);
        }

        $this->jsonResponse(['error' => true, 'message' => 'Something went wrong.'], 500);
    }
}

(new MarkAssetAsReturned())->handle();
