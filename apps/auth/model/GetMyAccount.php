<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class GetMyAccount
{
    public static function selectMyAccount($user_ID, string $table = 'users'): ?array
    {
        $stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE user_ID = :ud LIMIT 1");
        $stmt->bindParam(':ud', $user_ID, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? self::withoutSensitiveFields($result) : null;
    }

    private static function withoutSensitiveFields(array $user): array
    {
        foreach (array_keys($user) as $key) {
            $normalizedKey = strtolower((string)$key);
            if (preg_match('/password|verification_code|token|secret|otp|pin|reset/', $normalizedKey)) {
                unset($user[$key]);
            }
        }

        return $user;
    }
}
