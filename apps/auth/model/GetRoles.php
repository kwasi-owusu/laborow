<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';
class GetRoles
{
    //select all user roles
    static public function allRoles($table, $my_role){
        $sysRole        = "System";
        $merchantRole   = "Merchant";

        if($my_role == 1){
        $stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE applicable_package = :sys ORDER BY role_desc ASC");
        $stmt->bindParam('sys', $sysRole, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
        }

        else if($my_role == 2){
            $stmt = Connection::connect()->prepare("SELECT * FROM $table WHERE applicable_package = :mch ORDER BY role_desc ASC");
        $stmt->bindParam('mch', $merchantRole, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
        }
    }

}