<?php

require_once dirname(__DIR__) . '/controller/ISecureListingInterface.php';
require_once dirname(__DIR__, 2) . '/auth/model/MDLSecureLogin.php';

class CTRLSecureListing implements ISecureListingInterface{

    public function is_form_hash_valid(string $page_name, string $hash_key) : string {
        
        $page_is        = $page_name;
        $thi_is_is      = $hash_key;
        $rock_hash      = $page_is.$thi_is_is;

        $loginTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $loginTkn;
        
    }

    public function is_email_verified(string $user_email) : array{

        $table = 'users';
        $check_if_password_is_valid = new MDLSecureLogin();

        $getRst = $check_if_password_is_valid->is_email_verified_mdl($user_email, $table);

        return $getRst;
    }
    
    public function is_identity_verified(string $user_id) : array{

        $table = 'users';
        $check_if_password_is_valid = new MDLSecureLogin();

        $getRst = $check_if_password_is_valid->is_identity_verified_mdl($user_id, $table);

        return $getRst;
    }

}