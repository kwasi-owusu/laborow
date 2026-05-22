<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class CheckUserEmail
{
    public function is_email_already_exists_mdl($user_email, $table): int
    {
        if ($table !== 'users') {
            throw new InvalidArgumentException('Invalid table name.');
        }

        $stmt = Connection::connect()->prepare('SELECT 1 FROM users WHERE LOWER(email) = LOWER(:em) LIMIT 1');
        $stmt->bindParam('em', $user_email, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchColumn() ? 1 : 0;
    }
}
