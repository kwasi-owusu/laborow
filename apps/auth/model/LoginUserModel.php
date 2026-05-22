<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

final class LoginUserModel
{
    /**
     * Authenticates a user with given email and hashed password.
     *
     * @param string $table User table name
     * @param array $data Associative array with keys 'em' (email) and 'ps' (hashed password)
     * @return array|null User data if found, null otherwise
     */
    public function MdlShowUsers(string $table, string $unused, array $data): ?array
    {
        try {
            if ($table !== 'users') {
                throw new InvalidArgumentException("Invalid table name.");
            }

            $pdo = Connection::connect();
            $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = :email AND password = :password LIMIT 1");
            $stmt->bindParam(':email', $data['em'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $data['ps'], PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user ? self::withoutSensitiveFields($user) : null;
        } catch (PDOException | InvalidArgumentException $e) {
            error_log("Login DB Error: " . $e->getMessage());
            return null;
        }
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
