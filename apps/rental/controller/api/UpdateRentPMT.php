<?php

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once __DIR__ . '/v_pmt.php';

class UpdateRentPMT
{
    public function update_rent_pmt()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $dt = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($dt)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = [
            'asset_id',
            'reference',
            'pmt_status',
            'payment_method'
        ];

        foreach ($required_fields as $field) {
            if (empty($dt[$field]) || $dt[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }

        foreach ($dt as $k => $v) {
            $dt[$k] = self::sanitize((string) $v);
        }

        if (!ctype_digit((string) $dt['asset_id'])) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid asset ID.'], 400);
        }

        $model = new Rentals();
        $incomingStatus = strtolower(trim($dt['pmt_status']));
        $clientClaimsPaid = in_array($incomingStatus, ['paid', 'success'], true);

        if ($clientClaimsPaid) {
            $rentPayment = $model->get_rent_payment_details((int) $dt['asset_id'], $dt['reference']);
            if (!$rentPayment) {
                self::jsonResponse(['error' => true, 'message' => 'Rental payment reference not found.'], 404);
            }

            $verifyPMT = new VerifyPMT();
            $verificationResponse = $verifyPMT->vf_pmt(['reference' => $dt['reference']]);

            if (!$verificationResponse['status']) {
                self::jsonResponse(['error' => true, 'message' => $verificationResponse['message']], 502);
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

            $dt['pmt_status'] = $verifiedStatus;
            $dt['payment_method'] = self::sanitize((string) ($verifiedData->channel ?? $dt['payment_method']));
        }

        $saved = $model->update_rent_pmt_status([
            'asset_id' => $dt['asset_id'],
            'reference' => $dt['reference'],
            'pmt_status' => $dt['pmt_status'],
            'payment_method' => $dt['payment_method']
        ]);

        if ($saved) {
            self::jsonResponse([
                'error' => false,
                'message' => $clientClaimsPaid
                    ? 'Payment verified and rent updated successfully.'
                    : 'Rent payment status updated successfully.',
                'payment_status' => $dt['pmt_status'],
                'payment_method' => $dt['payment_method']
            ]);
        }

        self::jsonResponse(['error' => true, 'message' => 'Failed to save rent payment status.'], 500);
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
(new UpdateRentPMT())->update_rent_pmt();