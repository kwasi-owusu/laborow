<?php
require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class MDLSecureLogin extends Connection
{
    
   
    public function is_password_valid_mdl($user_id, $hashed_password, $table)
    {

        $newPDO = new Connection();
        $thisPDO = $newPDO->Connect();

        $stmt = $thisPDO->prepare("SELECT * FROM $table WHERE user_id = :uid AND password = :password ORDER BY password_log_id DESC LIMIT 1");
        $stmt->bindValue(':uid', $user_id, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    
    public function is_email_verified_mdl($user_email, $table)
    {

        $newPDO = new Connection();
        $thisPDO = $newPDO->Connect();

        $stmt = $thisPDO->prepare("SELECT * FROM $table WHERE email = :em LIMIT 1");
        $stmt->bindValue(':em', $user_email, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function is_identity_verified_mdl($user_id, $table)
    {

        $newPDO = new Connection();
        $thisPDO = $newPDO->Connect();

        $stmt = $thisPDO->prepare("SELECT * FROM $table WHERE user_id = :d LIMIT 1");
        $stmt->bindValue(':d', $user_id, PDO::PARAM_STR);
        
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
