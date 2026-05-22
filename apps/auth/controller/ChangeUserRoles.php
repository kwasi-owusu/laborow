<?php

session_start();
class ChangeUserRoles
{
    static public function changeRole(){
        // request model here
        require_once('../../model/users/UserModel.php');
        $error = false;
        $getToken   = trim($_POST['change_role_tkn']);
        if (isset($_SESSION['changeRoleToken']) && $_SESSION['changeRoleToken'] == $getToken) {
            $user_ID = trim($_POST['user_ID']);
            $userRole = trim($_POST['change_rle']);

            if ($userRole == '999') {
                $error = true;
                echo "<span style='color: #b9090e'>You need to select a new Role</span>";
            }
            elseif (!$error) {
                $tbl            = 'users_tbl';
                $lastUpdateBy   = '1';
                $lastUpdateOn   = Date('Y-m-d');
                $data = array(
                    'ud' => $user_ID,
                    'adb' => '1',
                    'ur' => $userRole,
                    'lb'=>$lastUpdateBy,
                    'ln'=>$lastUpdateOn
                );

                if (UserModel::changeUserRole($tbl, $data)){
                    echo "<span style='color: #1b901d'>Update Successful.</span>";
                } else {
                    echo "<span style='color: #b9090e'>Update Unsuccessful</span>";
                }

            }
        }
        else {
            echo "<span style='color: #b9090e'>Action Not Permitted</span> ".$_SESSION['changeRoleToken'];
        }
}
}
$callMethod     = new ChangeUserRoles();
$thisMethod     = $callMethod->changeRole();