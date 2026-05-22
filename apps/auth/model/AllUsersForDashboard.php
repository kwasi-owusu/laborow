<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';
class AllUsersForDashboard
{
    static public function getActiveUsers(){
        $stmt   = Connection::connect()->prepare("SELECT * FROM users_tbl WHERE userStatus = 1");
        $stmt->execute();

        return $stmt;
    }
}