<?php
session_start();

class AddUserRoles
{
    static public function createUserRole(){
        require_once('../../model/users/UserModel.php');
        $tkn = trim($_POST['role_tkn']);
        $error = false;

        if (isset($_SESSION['addUserRoleTkn']) && $_SESSION['addUserRoleTkn'] == $tkn){
            $role_name  = trim($_POST['roleName']);
            $role_desc  = trim($_POST['role_desc']);

            if (empty($role_name)){
                $error = true;
                echo "<span style='color: #b9090e'>Role Name Cannot be empty</span >";
            }

            elseif (empty($role_desc)){
                $error = true;
                echo "<span style='color: #b9090e'>Role Description Cannot be empty</span >";
            }

            elseif (!$error){
                $tbl    = 'user_role';
                $data   = array(
                  'rn'=>$role_name,
                  'rd'=>$role_desc,
                  'adb'=>'1'
                );
                if (UserModel::addRole($tbl, $data)){
                    echo "<span style='color: #1b901d'>Entry Successful.</span>";
                } else {
                    echo "<span style='color: #b9090e'>Entry Unsuccessful</span>";
                }
            }
        }
    }
}
$callMethod     = new AddUserRoles();
$thisMethod     = $callMethod->createUserRole();