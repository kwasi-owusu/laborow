<?php
class CountriesCtrl{
    public static function getAllCountries(){
        
        require_once '../../settings/model/Countries.php';
                
        $getRst     = Countries::allCountries();

        return $getRst;
    }   
}

?>