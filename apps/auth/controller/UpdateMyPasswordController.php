<?php

session_start();
class UpdateMyPasswordController
{
    public static function changeMyPassword(){
        $getToken   = strip_tags(trim($_POST['tkn']));
        $error      = false;
        if (isset($_SESSION['editUserToken']) && $_SESSION['editUserToken'] == $getToken){
            $user_ID    = strip_tags(trim($_POST['user_ID']));
            $user_pwd   = trim($_POST['c_password']);
            $phone_number = strip_tags(trim($_POST['phone_number']));

            if (empty($user_pwd)){
                $error  = true;
                echo "<span style='color: #b9090e'>User Password cannot be empty</span>";
            }
            elseif (empty($phone_number)){
                $error  = true;
                echo "<span style='color: #b9090e'>Phone number cannot be empty</span>";
            }

            elseif (!$error){
                require_once('../model/UserModel.php');
                $lastUpdateBy   = $_SESSION['uid'];;
                $lastUpdateOn   = Date('Y-m-d');
                $new_password = hash('sha256', $user_pwd);
                $tbl    = 'users';
                $data   = array(
                    'ud'=>$user_ID,
                    'phn'=> $phone_number,
                    'npd'=>$new_password,
                    'lb'=>$lastUpdateBy,
                    'ln'=>$lastUpdateOn
                );
                if (UserModel::updateUserPwd($tbl, $data)){
                    echo "Update Successful. ";
                } else {
                    echo "Update Unsuccessful";
                }
            }
        }
        else{
            echo "<span style='color: #b9090e'>Action not Permitted</span >";
        }
    }
}

UpdateMyPasswordController::changeMyPassword();