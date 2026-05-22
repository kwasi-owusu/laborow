<?php


class DoUserCors
{

    static public function loginCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $loginTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);


        return $loginTkn;
    }


    static public function addUserCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $userTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $userTkn;
    }


    static public function addUserRoleCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $userRoleTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $userRoleTkn;
    }


    static public function changeUserRoleCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $changeRoleTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $changeRoleTkn;
    }

    static public function editUserCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $editUserDetailsTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $editUserDetailsTkn;
    }


    static public function changeUserStatusCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $userStatusTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $userStatusTkn;
    }

    static public function changeUserSPasswordCors($page_name){
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is.$thi_is_is;

        $userStatusTkn = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $userStatusTkn;
    }
}