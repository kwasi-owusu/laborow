<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';
require_once dirname(__DIR__, 3) . '/rental/model/Rentals.php';

final class Callback
{
    public function getStatus(): void
    {
        // 1. Accept only POST with Paystack Signature
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            http_response_code(400);
            exit('Invalid request');
        }

        // 2. Read raw payload
        $input = @file_get_contents('php://input');
        if (!$input) {
            http_response_code(400);
            exit('Empty request body');
        }

        // 3. Validate secret key
        $secret = getenv('PAYSTACK_SECRET_KEY') ?: getenv('SECRET_KEY');
        if (!$secret) {
            error_log('[Paystack Webhook] PAYSTACK_SECRET_KEY not set in environment');
            http_response_code(500);
            exit();
        }

        // 4. Verify HMAC-SHA512 signature
        $computedHash = hash_hmac('sha512', $input, $secret);
        if (!hash_equals($computedHash, $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'])) {
            error_log('[Paystack Webhook] Invalid Signature: Possible spoof attempt.');
            http_response_code(403);
            exit('Invalid signature');
        }

        // 5. Decode payload
        $event = json_decode($input);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($event->event)) {
            error_log('[Paystack Webhook] Invalid JSON payload');
            http_response_code(400);
            exit('Invalid JSON payload');
        }

        // 6. Immediately respond to Paystack to prevent timeout
        http_response_code(200);

        // 7. Log a sanitized webhook summary for auditing
        $this->logPayload($event);

        // 8. Handle successful charge
        if ($event->event === 'charge.success' && ($event->data->status ?? '') === 'success') {
            $reference = $event->data->reference ?? null;
            $channel = $event->data->channel ?? 'unknown';
            $metadata = $event->data->metadata ?? null;
            $asset_id = self::getAssetIdFromMetadata($metadata);

            if ($reference && $asset_id) {
                $rentals = new Rentals();
                $updated = $rentals->update_rent_pmt_status([
                    'pmt_status'     => 'success',
                    'payment_method' => $channel,
                    'asset_id'       => $asset_id,
                    'reference'      => $reference
                ]);

                if (!$updated) {
                    error_log('[Paystack Webhook] Failed to update rent for reference: ' . self::maskIdentifier((string)$reference));
                }
            } else {
                error_log('[Paystack Webhook] Missing reference or asset_id in metadata');
            }
        }
    }

    /**
     * Extract asset_id from metadata object/array.
     */
    private static function getAssetIdFromMetadata($metadata): ?int
    {
        if (is_array($metadata) && isset($metadata['asset_id'])) {
            return (int)$metadata['asset_id'];
        }

        if (is_object($metadata) && isset($metadata->asset_id)) {
            return (int)$metadata->asset_id;
        }

        return null;
    }

    /**
     * Save a sanitized webhook summary without raw customer or authorization data.
     */
    private function logPayload(object $event): void
    {
        $data = is_object($event->data ?? null) ? $event->data : null;
        $metadata = $data ? ($data->metadata ?? null) : null;

        $logLine = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event->event ?? '',
            'status' => $data->status ?? '',
            'reference' => self::maskIdentifier((string)($data->reference ?? '')),
            'asset_id' => self::getAssetIdFromMetadata($metadata),
            'channel' => $data->channel ?? '',
            'amount' => $data->amount ?? null,
            'currency' => $data->currency ?? ''
        ];

        file_put_contents(__DIR__ . '/callback.log', json_encode($logLine, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
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
}

(new Callback())->getStatus();
