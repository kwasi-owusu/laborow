<?php
!isset($_SESSION) ? session_start() : null;
class CTRLUserLogin
{

    private string $table_a;
    private string $table_b;

    public function __construct($table_a, $table_b)
    {
        $this->table_a = $table_a;
        $this->table_b = $table_b;
    }

    public function UserLoginCTRL()
    {
        $error      = false;
        $login_user_tkn    = trim(strip_tags($_POST['lgn-tkn']));

        $val = trim(strip_tags($_POST["lgnUser"]));
        if (isset($_SESSION['login_tkn']) && $_SESSION['login_tkn'] == $login_user_tkn) {

            $user_email     = trim(strip_tags($_POST['lgnUser']));
            $user_password  = trim(strip_tags($_POST['lgnPwd']));

            if (empty($user_email)) {
                $error = true;
                echo "User Name is required";

                return;
            } elseif (empty($user_password)) {
                $error = true;
                echo "Password is required";

                return;
            }

            if (!$error) {


                require_once dirname(__DIR__) . '/controller/CTRLSecureLogin.php';
                require_once dirname(__DIR__) . '/controller/AuthEnums.php';

                require_once dirname(__DIR__) . '/model/LoginUserModel.php';
                require_once dirname(__DIR__) . '/model/MDLUserActivities.php';

                $password_hash_key      = LaborowHashKeys::password_hash->value;
                $hashed_password        = hash_hmac('sha512', $user_password,  $password_hash_key);

                $data   = array(
                    'em' => $user_email,
                    'ps' => $hashed_password
                );


                $login_obj = new MDLUserActivities();

                $this_user = new LoginUserModel();

                $fetch_user = $this_user->MdlShowUsers($this->table_a, $this->table_b, $data);

                $count_rows = $fetch_user->rowCount();


                if ($count_rows > 0) {

                    $user = $fetch_user->fetch(PDO::FETCH_ASSOC);
                    $user_status            = isset($user['userStatus']) ? $user['userStatus'] : null;
                    $user_id                = isset($user['user_id']) ? $user['user_id'] : null;
                    $user_access_level      = isset($user['user_access_level']) ? $user['user_access_level'] : null;


                    if ($user_status == 0) {

                        $activities = array(
                            'actions' => 'Login Attempted',
                            'status' => 'Failed',
                            'usernames' => $user_email
                        );

                        $activity_desc = json_encode($activities);

                        $activity_data = array(
                            'activity_module' => 'User Login',
                            'activity_desc' => $activity_desc,
                            'user_id' => $user_email
                        );

                        $save_activities = $login_obj->userActivitiesMDL($activity_data, $this->table_b);


                        $message        = "User Access Denied";
                        $error_code     = 112;

                        $response_msg   = array(
                            'error' => true,
                            'message' => $message,
                            'error_code' => $error_code
                        );

                        echo json_encode($response_msg);

                        return;
                    } elseif ($user_status == 1) {

                        //check if password is still valid
                        $instance_for_password_security = new CTRLSecureLogin();

                        $fetchPasswords = $instance_for_password_security->is_password_valid($user_id, $hashed_password);
                        $password_expiry_check = PasswordSecurity::password_expires_after_days->value;

                        $password_date  = $fetchPasswords['system_date'];
                        $todays_date    = Date('Y-m-d');


                        function passwordNumberOfDays($password_date, $todays_date)
                        {

                            $diff = strtotime($todays_date) - strtotime($password_date);


                            return abs(round($diff / 86400));
                        }


                        $password_number_of_days = passwordNumberOfDays($password_date, $todays_date);

                        if ($password_number_of_days >= $password_expiry_check) {

                            $activities = array(
                                'actions' => 'Login Attempted',
                                'status' => 'Failed',
                                'usernames' => $user_email
                            );

                            $activity_desc = json_encode($activities);

                            $activity_data = array(
                                'activity_module' => 'User Login. Password Expired',
                                'activity_desc' => $activity_desc,
                                'user_id' => $user_email
                            );

                            $save_activities = $login_obj->userActivitiesMDL($activity_data, $this->table_b);

                            echo "Password expired. Please check your ";

                            echo "<script>
                            window.location = 'change_password';
                            </script>";

                            echo "Password Expired";

                            return;
                        } elseif ($password_number_of_days < $password_expiry_check) {

                            $_SESSION['user_id']        = isset($user['user_id']) ? $user['user_id'] : null;
                            $_SESSION['first_name']     = isset($user['first_name']) ? $user['first_name'] : null;
                            $_SESSION['last_name']      = isset($user['last_name']) ? $user['last_name'] : null;
                            $_SESSION['email']          = isset($user['email']) ? $user['email'] : null;
                            $_SESSION['user_access_level']      = isset($user['user_access_level']) ? $user['user_access_level'] : null;
                            
                            $_SESSION["isLogin"] = 1;

                            //echo "Login Successful";

                            $message        = "Login Successful";
                            $error_code     = 111;

                            $response_msg   = array(
                                'error' => false,
                                'message' => $message,
                                'code' => $error_code
                            );

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

                            echo json_encode($response_msg);

                            // echo "<script>
                            // window.location = 'account';
                            // </script>";
                        }
                    }
                } else {

                    $activities = array(
                        'actions' => 'Login Attempted',
                        'status' => 'Failed',
                        'usernames' => $user_email
                    );

                    $activity_desc = json_encode($activities);

                    $activity_data = array(
                        'activity_module' => 'User Login',
                        'activity_desc' => $activity_desc,
                        'user_id' => 0
                    );

                    $save_activities = $login_obj->userActivitiesMDL($activity_data, $this->table_b);

                    $message        = "Login Unsuccessful.. $hashed_password Not in table " . $this->table_a;
                    $error_code     = 112;

                    $response_msg   = array(
                        'error' => true,
                        'message' => $message,
                        'error_code' => $error_code
                    );

                    echo json_encode($response_msg);

                    return;
                }
            } else {
                echo "Action Not Permitted " . $_SESSION['login_tkn'];
                $userIS         = $val;
                $activityDesc   = "Login Attempt";
                //$saveActivity   = ActivitiesCTRL::CTRLActivities($userIS, $activityDesc);
            }
        }
    }
}

$callClass = new CTRLUserLogin('users', 'user_activities');
$callMethod = $callClass->UserLoginCTRL();
