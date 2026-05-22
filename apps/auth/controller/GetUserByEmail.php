<?php


class GetUserByEmail
{
    static public function callUserByEmail($user_email){
        $getEmail   = $user_email;
        require_once('../model/CheckUserEmail.php');
        $tbl       = 'users';
        $checkUserEmail = CheckUserEmail::checkUser($tbl, $getEmail);

        return $checkUserEmail;
    }
}