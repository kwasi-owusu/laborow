<?php
session_start();
class UpdateUserController
{
    static public function updateUser(){
        require_once('../model/UserModel.php');
        $tkn = trim($_POST['tkn']);
        $error = false;
        if (isset($_SESSION['editUserToken']) && $_SESSION['editUserToken'] == $tkn){
            $firstName      = strip_tags(trim($_POST['fname']));
            $lastName       = strip_tags(trim($_POST['lname']));
            $user_email     = strip_tags(trim($_POST['user_email']));
            $user_role      = strip_tags(trim($_POST['rle']));
            $user_ID        = strip_tags(trim($_POST['user_ID']));
            $phone_number   = strip_tags(trim($_POST['phone_number']));


            //check if there is no required fields are empty
            if (empty($firstName)){
                $error = true;
                echo "<span>First Name Cannot be empty</span >";
            }

            elseif (empty($lastName)){
                $error = true;
                echo "<span>Lst Name Cannot be empty</span >";
            }

            elseif (empty($user_email)){
                $error = true;
                echo "<span>Email Cannot be empty</span >";
            }

            elseif (empty($user_role)){
                $error = true;
                echo "<span>User Role Cannot be empty</span > ". $user_role;
            }

            elseif (!$error){
                $tbl    = 'users';
                $last_update_on = Date('Y-m-d');
                //deactivate user account
                //1 = active; 2 = deactivated
                $userStatus     = 2;
                $data   = array(
                    'lbd'=> $_SESSION['uid'],
                    'fn'=>$firstName,
                    'ln'=>$lastName,
                    'em'=>$user_email,
                    'rl'=>$user_role,
                    'ud'=>$user_ID,
                    'nn'=>$last_update_on,
                    'phn' => $phone_number
                );

                if (UserModel::editUserDetails($tbl, $data)){
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

UpdateUserController::updateUser();