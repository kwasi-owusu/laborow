<?php
session_start();

class UpdateUserStatusController
{
    static public function changeStatus(){
        require_once('../model/UserModel.php');
        $tkn = trim($_POST['tkn']);
        $error = false;
        if (isset($_SESSION['editUserToken']) && $_SESSION['editUserToken'] == $tkn){
            $user_ID        = trim($_POST['user_ID']);
            $u_status       = trim($_POST['u_status']);

            if (empty($u_status)){
                $error = true;
                echo "<span>User Status Cannot be empty</span >";
            }

            elseif (!$error){
                $tbl            = 'users';
                $lastUpdateBy   = $_SESSION['uid'];
                $lastUpdateOn   = Date('Y-m-d');
                $data           = array(
                    'ud' => $user_ID,
                    'ust'=>$u_status,
                    'lbd'=>$lastUpdateBy,
                    'lbn'=>$lastUpdateOn
                );
                if (UserModel::updateUserStatus($tbl, $data)){
                    echo "<span>Update Successful.</span>";
                } else {
                    echo "<span>Update Unsuccessful</span>";
                }
            }
        }
        else{
            echo "<span>Action not Permitted</span >";
        }
    }
}

UpdateUserStatusController::changeStatus();