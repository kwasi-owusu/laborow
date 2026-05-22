<?php


class AllUsersForDashboardCtr
{
    static public function getAllUserForDash(){
        require_once '../../model/users/AllUsersForDashboard.php';
        $getRst     = AllUsersForDashboard::getActiveUsers();

        return $getRst;
    }
}