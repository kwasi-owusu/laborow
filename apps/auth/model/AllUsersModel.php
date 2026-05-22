<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';
class AllUsersModel
{
    //select all user
    static public function allUsers($merchant_ID, $myRole, $user_ID){

        if($myRole == 1){
            $stmt = Connection::connect()->prepare("SELECT users.*, user_roles.*, merchants.*
        FROM users
        INNER JOIN user_roles ON users.userRole = user_roles.role_ID 
        INNER JOIN merchants ON users.merchant_ID = merchants.merchant_ID
        ORDER BY users.firstName ASC");
        $stmt->execute();

        return $stmt;
        }

        elseif($myRole == 2){
        $stmt = Connection::connect()->prepare("SELECT users.*, user_roles.*, branches.*
        FROM users
        INNER JOIN user_roles ON users.userRole = user_roles.role_ID 
        INNER JOIN branches ON users.branch_ID = branches.branch_ID
        WHERE users.merchant_ID = :md
        ORDER BY users.firstName ASC");
        $stmt->bindParam('md', $merchant_ID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt;
        }

        elseif($myRole == 8){
        
        $stmt = Connection::connect()->prepare("SELECT users.*, user_roles.*
        FROM users
        INNER JOIN user_roles ON users.userRole = user_roles.role_ID 
        WHERE users.merchant_ID = :md
        AND users.userRole != 2
        ORDER BY users.firstName ASC");
        $stmt->bindParam('md', $merchant_ID, PDO::PARAM_STR);
        $stmt->execute();
            
        }

        else{
            $stmt = Connection::connect()->prepare("SELECT users.*, user_roles.*
        FROM users
        INNER JOIN user_roles ON users.userRole = user_roles.role_ID 
        WHERE users.user_ID = :me");
        $stmt->bindParam('me', $user_ID, PDO::PARAM_STR);
        $stmt->execute();
        }
    }
}