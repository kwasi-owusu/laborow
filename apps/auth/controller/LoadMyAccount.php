<?php


class LoadMyAccount
{
    static public function fetchMyAccount($user_ID){
        require_once('../model/GetMyAccount.php');
        $thisUser   = GetMyAccount::selectMyAccount($user_ID);

        return $thisUser;
    }
}