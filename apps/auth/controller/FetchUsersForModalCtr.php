<?php


class FetchUsersForModalCtr
{
    static public function fetchAllUser($user_ID){
        require_once '../../model/users/FetchAllUsersForModalMDL.php';
        $thisUser   = FetchAllUsersForModalMDL::selectAllUser();

        return $thisUser;
    }
}