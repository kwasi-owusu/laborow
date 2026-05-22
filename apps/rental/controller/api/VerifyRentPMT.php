<?php

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once __DIR__ . '/v_pmt.php';

final class VerifyRentPMT
{
    public function verify(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = ['reference', 'amount', 'asset_id'];
        foreach ($required_fields as $field) {
            if (empty($input[$field]) || $input[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }

        foreach ($input as $k => $v) {
            $input[$k] = self::sanitize((string) $v);
        }

        if (!ctype_digit((string) $input['asset_id'])) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid asset ID.'], 400);
        }

        $rentals = new Rentals();
        $rentPayment = $rentals->get_rent_payment_details((int) $input['asset_id'], $input['reference']);
        if (!$rentPayment) {
            self::jsonResponse(['error' => true, 'message' => 'Rental payment reference not found.'], 404);
        }

        $verifyPMT = new VerifyPMT();
        $verificationResponse = $verifyPMT->vf_pmt(['reference' => $input['reference']]);

        if (!$verificationResponse['status']) {
            self::jsonResponse(['error' => true, 'message' => $verificationResponse['message']], 500);
        }

        $verifiedData = $verificationResponse['data'];
        $verifiedStatus = strtolower(trim((string) ($verifiedData->status ?? '')));
        if ($verifiedStatus !== 'success') {
            self::jsonResponse([
                'error' => true,
                'message' => 'Payment has not been confirmed by Paystack.',
                'payment_status' => $verifiedStatus
            ], 400);
        }

        if (!self::paystackAmountMatchesRent($verifiedData, $rentPayment)) {
            self::jsonResponse([
                'error' => true,
                'message' => 'Payment amount does not match this rental.'
            ], 400);
        }

        $updateData = [
            'pmt_status'     => $verifiedStatus,
            'payment_method' => $verifiedData->channel ?? 'unknown',
            'asset_id'       => $input['asset_id'],
            'reference'      => $input['reference']
        ];

        $updated = $rentals->update_rent_pmt_status($updateData);

        if ($updated) {
            self::jsonResponse([
                'error' => false,
                'message' => 'Payment verified and rent updated successfully.',
                'payment_status' => $verifiedStatus,
                'channel' => $verifiedData->channel ?? 'unknown'
            ]);
        } else {
            self::jsonResponse([
                'error' => true,
                'message' => 'Payment verified, but failed to update rent status.'
            ], 500);
        }
    }

    private static function paystackAmountMatchesRent(object $verifiedData, array $rentPayment): bool
    {
        if (!isset($verifiedData->amount)) {
            return false;
        }

        $expectedAmount = (int) round((float) $rentPayment['total_amt'] * 100);
        $paidAmount = (int) $verifiedData->amount;
        return $expectedAmount === $paidAmount;
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new VerifyRentPMT())->verify();