<?php
if(!isset($_SESSION)) {
    session_start();
}
require_once('../../model/GetThisUser.php');
class GetThisUserController
{
    static public function userRoles($user_ID){
        $thisRole   = GetThisUser::thisRoles($user_ID);
        return $thisRole;
    }

    static public function fetchAllRoles(){
        $table = "user_roles";
        $my_role = $_SESSION['user_type'];
        $allRole   = GetThisUser::getAllRoles($table, $my_role);

        return $allRole;
    }

    static public function fetchThisUser($user_ID){
        $thisUser   = GetThisUser::selectThisUser($user_ID);

        return $thisUser;
    }
}

