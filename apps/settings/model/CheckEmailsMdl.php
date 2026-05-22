<?php

require_once 'apps/template/statics/conn/anthrax.php';

class CheckEmailsMdl{
    public static function checkCustomerEmail($val, $merchant_id){
        $stmt   = Connection::connect()->prepare("SELECT * FROM customers WHERE customa_email = :cem LIMIT 1");
        $stmt->bindParam(':cem',$val, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt;
    }

    public static function checkSupplierEmail($val, $merchant_id){
        $stmt   = Connection::connect()->prepare("SELECT * FROM suppliers WHERE supp_email = :cem LIMIT 1");
        $stmt->bindParam(':cem',$val, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt;
    }

    public static function checkUserEmail($val, $merchant_id){
        $stmt   = Connection::connect()->prepare("SELECT * FROM users WHERE userEmail = :cem LIMIT 1");
        $stmt->bindParam(':cem',$val, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt;
    }
    
    public static function checkContactEmail($val, $merchant_id){
        $stmt   = Connection::connect()->prepare("SELECT * FROM contacts WHERE contact_email = :cem LIMIT 1");
        $stmt->bindParam(':cem',$val, PDO::PARAM_STR);
       
        $stmt->execute();

        return $stmt;
    }

    public static function checkMerchantEmail($val, $merchant_id){
        $stmt   = Connection::connect()->prepare("SELECT * FROM merchants WHERE email = :cem LIMIT 1");
        $stmt->bindParam(':cem',$val, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt;
    }
}

?>