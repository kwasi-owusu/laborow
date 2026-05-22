<?php
//require_once '../model/CheckEmailsMdl.php';
require_once dirname(__DIR__).'/model/CheckEmailsMdl.php';

session_start();
class CheckEmails extends CheckEmailsMdl{
    
    public static function checkThisEmail(){
        $email_to_check = strip_tags(trim($_POST['email']));

        if (isset($email_to_check)){
            $where_to_check = strip_tags(trim($_POST['check_where']));
            

            if($where_to_check == "customers"){

                $val    = $email_to_check;
                $merchant_id    = $_SESSION['merchant_ID'];
                $getRst = CheckEmailsMdl::checkCustomerEmail($val, $merchant_id);

                $cnt_email = $getRst->rowCount();
                if($cnt_email > 0){
                    echo "Email Exists";
                }
                else{
                    echo "Email Not Exists";
                }
            }

            elseif($where_to_check == "users"){
                $val    = $email_to_check;
                $merchant_id    = $_SESSION['merchant_ID'];
                $getRst = CheckEmailsMdl::checkUserEmail($val, $merchant_id);

                $cnt_email = $getRst->rowCount();
                if($cnt_email > 0){
                    echo "Email Exists";
                }
                else{
                    echo "Email Not Exists";
                }
            }

            elseif($where_to_check == "contacts"){
                $val            = $email_to_check;
                $merchant_id    = $_SESSION['merchant_ID'];
                $getRst = CheckEmailsMdl::checkContactEmail($val, $merchant_id);

                $cnt_email = $getRst->rowCount();
                if($cnt_email > 0){
                    echo "Email Exists";
                }
                else{
                    echo "Email Not Exists";
                }
            }


        }
    }
}

CheckEmails::checkThisEmail();