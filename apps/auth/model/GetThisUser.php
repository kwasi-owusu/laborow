<?php
require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class GetThisUser
{
    //select role for a specific user
    static public function thisRoles($user_ID)
    {
        $stmt = Connection::connect()->prepare("SELECT users.*, user_roles.*
        FROM users
        INNER JOIN user_roles ON users.userRole = user_roles.role_ID
        WHERE users.user_ID = :ud
        ");
        $stmt->bindParam('ud', $user_ID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
    }

    //select this user
    public static function selectThisUser($user_ID): ?array
    {
        $stmt = Connection::connect()->prepare("SELECT * FROM users WHERE user_id = :ud LIMIT 1");
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

    //select all user roles
    static public function getAllRoles($table, $my_role)
    {
        $sysRole        = "System";
        $merchantRole   = "Merchant";

        if ($my_role == 1) {
            $stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE applicable_package = :sys ORDER BY role_desc ASC");
            $stmt->bindParam('sys', $sysRole, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt;
        } else if ($my_role == 2) {
            $stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE applicable_package = :mch ORDER BY role_desc ASC");
            $stmt->bindParam('mch', $merchantRole, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt;
        }
    }
}
