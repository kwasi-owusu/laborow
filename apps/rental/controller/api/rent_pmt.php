<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';

class process_payment
{
    public function make_pmt(array $data): array
    {
        $secretKey = $this->paystackSecretKey();
        if ($secretKey === null) {
            error_log('Paystack secret key is not configured. Set PAYSTACK_SECRET_KEY or SECRET_KEY.');
            return ['status' => false, 'message' => 'Payment service is not configured.'];
        }

        $pst_url = "https://api.paystack.co/transaction/initialize";

        $fields = [
            'email' => $data['user_email'],
            'amount' => (int) round((float) $data['total_amt'] * 100)
        ];

        if (!empty($data['reference'])) {
            $fields['reference'] = $data['reference'];
        }

        if (!empty($data['metadata']) && is_array($data['metadata'])) {
            $fields['metadata'] = $data['metadata'];
        }

        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $secretKey"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $pst_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($fields),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);

        $result = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            error_log("Paystack init cURL Error: $err");
            return ['status' => false, 'message' => 'Connection error.'];
        }

        $response = json_decode($result);

        if ($httpCode !== 200 || !$response || !isset($response->data->authorization_url)) {
            error_log('Paystack Init Error: ' . $result);
            return ['status' => false, 'message' => 'Payment initialization failed.'];
        }

        return [
            'status' => true,
            'authorization_url' => $response->data->authorization_url,
            'access_code' => $response->data->access_code ?? '',
            'reference' => $response->data->reference ?? ''
        ];
    }

    private function paystackSecretKey(): ?string
    {
        $secretKey = getenv('PAYSTACK_SECRET_KEY') ?: getenv('SECRET_KEY') ?: '';
        $secretKey = trim((string)$secretKey);
        return $secretKey !== '' ? $secretKey : null;
    }
}