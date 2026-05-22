<?php

require_once __DIR__ . '/controller/api/SendAppNotificationWithFCM.php';

class SendAppNotificationWithFCM
{
    public function sendPushNotification($deviceToken, $title, $body): string
    {
        $deviceToken = trim((string)$deviceToken);
        $title = trim((string)$title);
        $body = trim((string)$body);

        if ($deviceToken === '' || $title === '' || $body === '') {
            return json_encode([
                'error' => true,
                'message' => 'Device token, title, and body are required.'
            ]);
        }

        try {
            $response = (new FCMNotification())->send($deviceToken, $title, $body);
            return json_encode([
                'error' => false,
                'response' => $response
            ]);
        } catch (Throwable $e) {
            error_log('[Legacy FCM Wrapper] Failed to send notification: ' . $e->getMessage());

            return json_encode([
                'error' => true,
                'message' => 'Failed to send notification.'
            ]);
        }
    }
}
