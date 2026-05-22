<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';

class VerifyPMT
{
    private string $base_url = "https://api.paystack.co/transaction/verify/";

    public function vf_pmt(array $data): array
    {
        if (empty($data['reference']) || !is_string($data['reference'])) {
            return ['status' => false, 'message' => 'Invalid reference provided.'];
        }

        $secretKey = $this->paystackSecretKey();
        if ($secretKey === null) {
            error_log('Paystack secret key is not configured. Set PAYSTACK_SECRET_KEY or SECRET_KEY.');
            return ['status' => false, 'message' => 'Payment service is not configured.'];
        }

        $reference = urlencode($data['reference']);
        $url = $this->base_url . $reference;

        $headers = [
            "Authorization: Bearer $secretKey",
            "Cache-Control: no-cache",
            "Accept: application/json"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "GET"
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            error_log("Paystack cURL Error: $err");
            return ['status' => false, 'message' => 'Connection error.'];
        }

        $response = json_decode($result);

        if ($httpCode !== 200 || !$response || !$response->status || !isset($response->data)) {
            error_log("Paystack verify error: $result");
            return ['status' => false, 'message' => $response->message ?? 'Verification failed'];
        }

        return [
            'status' => true,
            'message' => 'Verification successful',
            'data' => $response->data
        ];
    }

    private function paystackSecretKey(): ?string
    {
        $secretKey = getenv('PAYSTACK_SECRET_KEY') ?: getenv('SECRET_KEY') ?: '';
        $secretKey = trim((string)$secretKey);
        return $secretKey !== '' ? $secretKey : null;
    }
}