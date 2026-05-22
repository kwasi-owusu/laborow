<?php
require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

class ConfirmAssetReceiptByRenter
{
    private function jsonResponse($data, $code = 200)
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
            $this->jsonResponse(['error' => true, 'message' => 'You are not allowed to confirm receipt for this rental.'], 403);
        }
    }

    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => true, 'message' => 'Use POST.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'));
        if (!is_object($input)) {
            $this->jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $rentId = $this->clean($input->current_rent_ID ?? '');

        if ($rentId === '' || !ctype_digit((string) $rentId)) {
            $this->jsonResponse(['error' => true, 'message' => 'Missing or invalid rent ID.'], 400);
        }

        $model = new Rentals();
        $this->enforceRenterIfTokenPresent($model, (int) $rentId);
        $res = $model->confirm_asset_receipt_by_renter($rentId);

        if ($res['status']) {
            $this->jsonResponse(['error' => false, 'message' => $res['message']]);
        } else {
            $this->jsonResponse(['error' => true, 'message' => $res['message']], 500);
        }
    }
}

(new ConfirmAssetReceiptByRenter())->handle();
