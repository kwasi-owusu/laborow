<?php

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';
require_once __DIR__ . '/rent_pmt.php';

final class RentThisCTRL
{
    public function rent(): void
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
            'user_ID',
            'user_email',
            'total_duration',
            'charge_type',
            'return_date',
            'start_date',
            'total_amt',
            'asset_owner_ID',
            'payment_method'
        ];

        foreach ($required_fields as $field) {
            if (empty($dt[$field]) || $dt[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }

        // Sanitize all input
        foreach ($dt as $k => $v) {
            $dt[$k] = self::sanitize((string)$v);
        }

        if (!ctype_digit((string)$dt['user_ID']) || !ctype_digit((string)$dt['asset_id'])) {
            self::jsonResponse(['error' => true, 'message' => 'User ID and asset ID must be numeric.'], 400);
        }

        $model = new Rentals();

        // Compatibility bridge: old mobile clients do not send a bearer token yet.
        // If a token is present, enforce renter identity and normalize asset ownership from DB.
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken !== null) {
            $authUser = ApiAuthToken::validate($bearerToken);
            if (!$authUser) {
                self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
            }

            if ((int)$authUser['user_id'] !== (int)$dt['user_ID']) {
                self::jsonResponse(['error' => true, 'message' => 'Token user does not match renter.'], 403);
            }

            $asset = $model->get_asset_rental_details((int)$dt['asset_id']);
            if (!$asset) {
                self::jsonResponse(['error' => true, 'message' => 'Asset not found.'], 404);
            }

            $dt['asset_owner_ID'] = (string)$asset['user_ID'];
            $dt['charge_type'] = (string)($asset['charge_type'] ?: $dt['charge_type']);
        }

        // Generate secure reference
        $payment_ref = hash_hmac(
            'sha256',
            uniqid((string) $dt['user_ID'], true),
            $dt['asset_id'] . $dt['start_date'] . $dt['return_date']
        );

        // Prepare data for Paystack
        $pmt_data = [
            'user_email' => $dt['user_email'],
            'total_amt'  => $dt['total_amt'],
            'reference'  => $payment_ref,
            'metadata'   => [
                'asset_id' => $dt['asset_id'],
                'user_ID' => $dt['user_ID'],
                'asset_owner_ID' => $dt['asset_owner_ID'],
                'start_date' => $dt['start_date'],
                'return_date' => $dt['return_date'],
            ]
        ];

        $payment = (new process_payment())->make_pmt($pmt_data);
        if (!$payment['status']) {
            self::jsonResponse([
                'error' => true,
                'message' => $payment['message'] ?? 'Payment initialization failed.'
            ], 500);
        }

        // Log response without dumping full Paystack payload/secrets.
        file_put_contents(__DIR__ . '/pmt.log', json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'asset_id' => $dt['asset_id'],
            'user_ID' => $dt['user_ID'],
            'reference' => self::maskIdentifier((string)($payment['reference'] ?? $payment_ref)),
            'status' => $payment['status'] ?? null,
        ], JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

        // Persist rent info
        $saved = $model->rent_this([
            'asset_id'       => $dt['asset_id'],
            'user_ID'        => $dt['user_ID'],
            'total_duration' => $dt['total_duration'],
            'charge_type'    => $dt['charge_type'],
            'start_date'     => $dt['start_date'],
            'return_date'    => $dt['return_date'],
            'total_amt'      => $dt['total_amt'],
            'asset_owner_ID' => $dt['asset_owner_ID'],
            'payment_method' => $dt['payment_method'],
            'payment_ref'    => $payment['reference']
        ]);

        if ($saved) {
            self::jsonResponse([
                'error' => false,
                'asset_id' => $dt['asset_id'],
                'message' => 'Rent saved successfully.',
                'authorization_url' => $payment['authorization_url'],
                'access_code' => $payment['access_code'],
                'reference' => $payment['reference'],
                'amount' => (float)$dt['total_amt']
            ]);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Failed to save rent.'], 500);
        }
    }

    private static function maskIdentifier(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (strlen($value) <= 10) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 6) . '...' . substr($value, -4);
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

(new RentThisCTRL())->rent();
