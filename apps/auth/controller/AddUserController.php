<?php
session_start();

class AddUserController
{

    private string $table_a;
    private string $table_b;

    public function __construct($table_a, $table_b)
    {
        $this->table_a = $table_a;
        $this->table_b = $table_b;
    }


    public function addUser(){
        
        $tkn = trim($_POST['tkn']);
        $error = false;
        if (isset($_SESSION['addUserTkn']) && $_SESSION['addUserTkn'] == $tkn){
            
            $firstName      = trim(strip_tags($_POST['fname']));
            $lastName       = trim(strip_tags($_POST['lname']));
            $user_email     = trim(strip_tags($_POST['lgnUser']));
            $country        = trim(strip_tags($_POST['country']));
            $state_region   = trim(strip_tags($_POST['state_region']));
            $city_town      = trim(strip_tags($_POST['city_town']));
            $user_pwd       = trim(strip_tags($_POST['lgnPwd']));
            $phone_number   = trim(strip_tags($_POST['phone_number']));
            $user_type      = trim(strip_tags($_POST['user_type']));
            
            $user_role      = 2;
           
            $key_details = $user_email."-".$phone_number."-".$user_pwd;

            $userKey = hash_hmac('sha512', $key_details, $user_email);



            //check if there is no empty
            if (empty($firstName)){
                $error = true;
                echo "First Name Cannot be empty";
            }

            elseif (empty($lastName)){
                $error = true;
                echo "Last Name Cannot be empty";
            }

            elseif (empty($user_email)){
                $error = true;
                echo "Email Cannot be empty";
            }

            elseif (empty($user_pwd)){
                $error = true;
                echo "Password Cannot be empty";
            }

           
            elseif (empty($user_role)){
                $error = true;
                echo "User Role Cannot be empty ". $user_role;
            }

            elseif (!$error){
                //check if user already exist
                require_once dirname(__DIR__) . '/model/UserModel.php';
                require_once dirname(__DIR__) . '/controller/GetUserByEmail.php';
                require_once dirname(__DIR__) . '/model/MDLUserActivities.php';

                require_once dirname(__DIR__) . '/controller/CTRLSecureLogin.php';
                require_once dirname(__DIR__) . '/controller/AuthEnums.php';

                $login_obj = new MDLUserActivities();

                $password_hash_key      = LaborowHashKeys::password_hash->value;
                $new_password           = hash_hmac('sha512', $user_pwd,  $password_hash_key);
                
                $instance_for_email_exist = new CTRLSecureLogin();
               
                $fetchPasswords = $instance_for_email_exist->is_email_already_exists($user_email);

               
                if ($fetchPasswords < 1) {
                    
                    $data = array(
                        'fn' => $firstName,
                        'ln' => $lastName,
                        'em' => $user_email,
                        'ctr' => $country,
                        'stt' => $state_region,
                        'cty' => $city_town,
                        'pd' => $new_password,
                        'rl' => $user_role,
                        'phn'=> $phone_number,
                        'ust' =>$user_type,
                        'phn' => $phone_number,
                        'usk' => $userKey
                    );

                    if (UserModel::addUser($this->table_a, $this->table_b, $data)) {
                        echo "Entry Successful.  Please check your email for confirmation mail";

                        $activities = array(
                            'actions' => 'Login Attempted',
                            'status' => 'Successful',
                            'usernames' => $user_email
                        );
    
                        $activity_desc = json_encode($activities);
    
                        $activity_data = array(
                            'activity_module' => 'User Login',
                            'activity_desc' => $activity_desc,
                            'user_id' => 0
                        );
    
                        $save_activities = $login_obj->userActivitiesMDL($activity_data, $this->table_b);

                    } else {
                        echo "Entry Unsuccessful<.";
                    }
                }
                else{
                    echo "User already exists";
                }
            }
        }
        else{
            echo "Action not Permitted";
        }
    }
}

$callClass = new AddUserController('users', 'user_activities');
$callMethod = $callClass->addUser();
