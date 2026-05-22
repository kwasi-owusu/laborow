<?php
session_start();
ob_start();
date_default_timezone_set('Africa/Accra');

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

class ConfirmAssetReturn
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

    private function ownerIdForRequest(Rentals $model, int $rentId, string $submittedOwnerId): string
    {
        $bearer = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearer === null) {
            return $submittedOwnerId;
        }

        $auth = ApiAuthToken::validate($bearer);
        if (!$auth) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
        }

        $rent = $model->get_rent_participants($rentId);
        if (!$rent) {
            $this->jsonResponse(['error' => true, 'message' => 'Rental not found.'], 404);
        }

        if ((int) $rent['asset_owner_ID'] !== (int) $auth['user_id']) {
            $this->jsonResponse(['error' => true, 'message' => 'You are not allowed to confirm return for this rental.'], 403);
        }

        return (string) $rent['asset_owner_ID'];
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

        $rentId  = $this->clean($input->current_rent_ID ?? '');
        $ownerId = $this->clean($input->owner_ID ?? '');
        $hasToken = ApiAuthToken::bearerFromServer($_SERVER) !== null;

        if ($rentId === '' || !ctype_digit((string) $rentId)) {
            $this->jsonResponse(['error' => true, 'message' => 'Missing or invalid rent ID.'], 400);
        }

        if (!$hasToken && ($ownerId === '' || !ctype_digit((string) $ownerId))) {
            $this->jsonResponse(['error' => true, 'message' => 'Missing rent ID or owner ID.'], 400);
        }

        $model = new Rentals();
        $ownerId = $this->ownerIdForRequest($model, (int) $rentId, $ownerId);
        $confirmed = $model->confirm_asset_return_by_owner($rentId, $ownerId);

        if ($confirmed['status'] === true) {
            $this->jsonResponse(['error' => false, 'message' => $confirmed['message']]);
        } else {
            $this->jsonResponse(['error' => true, 'message' => $confirmed['message']], 500);
        }
    }
}

(new ConfirmAssetReturn())->handle();
