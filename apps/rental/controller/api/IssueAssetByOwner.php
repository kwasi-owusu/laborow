<?php
require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

class IssueAssetByOwner
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

    private function enforceOwnerIfTokenPresent(Rentals $model, int $rentId): void
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

        if ((int) $rent['asset_owner_ID'] !== (int) $auth['user_id']) {
            $this->jsonResponse(['error' => true, 'message' => 'You are not allowed to issue this asset.'], 403);
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
        $notes = $this->clean($input->issue_notes ?? '');
        $photo = $this->clean($input->issue_photo ?? '');

        if ($rentId === '' || !ctype_digit((string) $rentId)) {
            $this->jsonResponse(['error' => true, 'message' => 'Missing or invalid rent ID.'], 400);
        }

        $model = new Rentals();
        $this->enforceOwnerIfTokenPresent($model, (int) $rentId);
        $res = $model->mark_asset_as_issued($rentId, $notes, $photo);

        if ($res['status']) {
            $this->jsonResponse(['error' => false, 'message' => $res['message']]);
        } else {
            $this->jsonResponse(['error' => true, 'message' => $res['message']], 500);
        }
    }
}

(new IssueAssetByOwner())->handle();
