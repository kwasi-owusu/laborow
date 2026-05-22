<?php

class GetAllUsers
{
    public static function doAllUsers(){
        $merchant_ID     = $_SESSION['merchant_ID'];
        $myRole = $_SESSION['user_type'];
        $user_ID = $_SESSION['user_id'];

        require_once('../model/AllUsersModel.php');

        $allUsers    = AllUsersModel::allUsers($merchant_ID, $myRole, $user_ID);

        return $allUsers;
    }
}