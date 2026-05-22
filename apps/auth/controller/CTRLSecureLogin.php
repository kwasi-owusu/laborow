<?php

require_once dirname(__DIR__) . '/controller/ISecureLoginInterface.php';
require_once dirname(__DIR__) . '/model/MDLSecureLogin.php';
require_once dirname(__DIR__) . '/model/CheckUserEmail.php';

class CTRLSecureLogin implements ISecureLoginInterface
{

    public function is_login_hash_valid(string $page_name, string $hash_key): string
    {

        $page_is        = $page_name;
        $thi_is_is      = $hash_key;
        $rock_hash      = $page_is . $thi_is_is;

        $loginTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);


        return $loginTkn;
    }


    public function is_password_valid(string $user_id, string $hashed_password): array
    {
        $table = 'password_logs';
        $check_if_password_is_valid = new MDLSecureLogin();

        $getRst = $check_if_password_is_valid->is_password_valid_mdl($user_id, $hashed_password, $table);

        return is_array($getRst) ? $getRst : [];
    }

    public function is_email_verified(string $user_email): array
    {

        $table = 'users';
        $check_if_password_is_valid = new MDLSecureLogin();

        $getRst = $check_if_password_is_valid->is_email_verified_mdl($user_email, $table);

        return $getRst;
    }

    public function is_identity_verified(string $user_id): array
    {

        $table = 'users';
        $check_if_password_is_valid = new MDLSecureLogin();

        $getRst = $check_if_password_is_valid->is_identity_verified_mdl($user_id, $table);

        return $getRst;
    }

    public function is_email_already_exists(string $user_email): int
    {

        $table = 'users';
        $check_if_email_already_exist = new CheckUserEmail();

        $getRst = $check_if_email_already_exist->is_email_already_exists_mdl($user_email, $table);

        return $getRst;
    }
}
