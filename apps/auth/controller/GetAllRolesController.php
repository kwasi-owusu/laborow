<?php

class GetAllRolesController
{
        public static function allRoles(){
            require_once('../model/GetRoles.php');

            $table = "user_roles";
            $my_role = $_SESSION['user_type'];

            $getRst   = GetRoles::allRoles($table, $my_role);

        return $getRst;
    }
}