<?php

require_once dirname(__DIR__, 3) . '/template/configs/bootstrap.php';

if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
    $base = dirname(__DIR__, 3) . '/template/statics/mailer/src/';
    $required = ['Exception.php', 'PHPMailer.php', 'SMTP.php'];
    foreach ($required as $file) {
        $path = $base . $file;
        if (!is_file($path)) {
            error_log('[SendEmail] Missing PHPMailer dependency: ' . $file);
            throw new RuntimeException('Email service dependency is missing.');
        }
        require_once $path;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class CTRLSendEmail
{
    public function send_email(array $data): bool
    {
        try {
            foreach (['recipient', 'subject', 'mail_body'] as $key) {
                if (empty($data[$key])) {
                    throw new InvalidArgumentException("Missing required email field: {$key}");
                }
            }

            $config = self::smtpConfig();
            if ($config === null) {
                error_log('[SendEmail] SMTP configuration is incomplete.');
                return false;
            }

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['user'];
            $mail->Password   = $config['pass'];
            $mail->CharSet    = 'UTF-8';

            if ($config['secure'] === 'starttls' || $config['secure'] === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $config['port'] ?: 587;
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = $config['port'] ?: 465;
            }

            $mail->setFrom($config['from_email'], $config['from_name']);

            $recipientName = (string)($data['recipient_name'] ?? '');
            $mail->addAddress((string)$data['recipient'], $recipientName);

            if ($config['reply_to'] !== '') {
                $mail->addReplyTo($config['reply_to'], $config['reply_to_name']);
            }

            if (!empty($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $key => $value) {
                    $path = is_int($key) ? $value : $key;
                    $name = is_int($key) ? '' : $value;
                    if (is_string($path) && is_file($path)) {
                        $mail->addAttachment($path, $name ?: basename($path));
                    }
                }
            }

            $mail->isHTML(true);
            $mail->Subject = (string)$data['subject'];
            $mail->Body    = (string)$data['mail_body'];
            $mail->AltBody = (string)($data['mail_body_alt'] ?? strip_tags((string)$data['mail_body']));

            if (self::env('SMTP_DEBUG') !== '' && (int)self::env('SMTP_DEBUG') > 0) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[SendEmail] PHPMailer exception: ' . self::safeError($e->getMessage()));
            return false;
        } catch (Throwable $e) {
            error_log('[SendEmail] Failed to send email: ' . self::safeError($e->getMessage()));
            return false;
        }
    }

    private static function smtpConfig(): ?array
    {
        $config = [
            'host' => self::env('SMTP_HOST'),
            'user' => self::env('SMTP_USER'),
            'pass' => self::env('SMTP_PASS'),
            'secure' => strtolower(self::env('SMTP_SECURE') ?: 'smtps'),
            'port' => (int)(self::env('SMTP_PORT') ?: 0),
            'from_email' => self::env('SMTP_FROM_EMAIL'),
            'from_name' => self::env('SMTP_FROM_NAME') ?: 'Tuetra',
            'reply_to' => self::env('SMTP_REPLY_TO'),
            'reply_to_name' => self::env('SMTP_REPLY_TO_NAME') ?: 'Support'
        ];

        foreach (['host', 'user', 'pass', 'from_email'] as $required) {
            if ($config[$required] === '') {
                return null;
            }
        }

        return $config;
    }

    private static function env(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return is_string($value) ? trim($value) : '';
    }

    private static function safeError(string $message): string
    {
        $message = preg_replace('/(password|pass|secret|token|key)=\S+/i', '$1=[redacted]', $message) ?? $message;
        return trim($message);
    }
}
