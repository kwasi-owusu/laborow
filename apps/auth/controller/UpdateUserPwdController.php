<?php
session_start();

class UpdateUserPwdController
{
    static public function changePwd(){
        require_once('../model/UserModel.php');
        $tkn = trim($_POST['tkn']);
        $error = false;
        if (isset($_SESSION['userPwdTkn']) && $_SESSION['userPwdTkn'] == $tkn){
            $user_ID        = trim($_POST['user_ID']);
            $password       = trim($_POST['user_pwd']);

            if (empty($password)){
                $error = true;
                echo "<span>Password Cannot be empty</span >";
            }

            elseif (!$error){
                $tbl            = 'users';
                $lastUpdateBy   = $_SESSION['uid'];
                $lastUpdateOn   = Date('Y-m-d');
                $new_password   = hash('sha256', $password);
                $data           = array(
                    'ud' => $user_ID,
                    'npd'=>$new_password,
                    'lbd'=>$lastUpdateBy,
                    'lbn'=>$lastUpdateOn
                );
                if (UserModel::updateUserPwd($tbl, $data)){
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
$callClass      = new UpdateUserPwdController();
$callMethod     = $callClass->changePwd();