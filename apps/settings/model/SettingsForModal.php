<?php

require_once '../../../template/statics/conn/connection.php';

class SettingsForModal
{
    public static function allCountriesForModal()
    {
            try {

                $stmt =  Connection::connect()->prepare("SELECT * FROM countries ORDER BY country_name ASC");
                $stmt->execute();
                
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                //echo $e->getMessage();
                echo "No countries were selected";
            }
    }

    public static function allStates($val, $tbl){
        try {

            $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE country_id = :ctd ORDER BY name ASC");
            $stmt->bindParam('ctd', $val, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            //echo $e->getMessage();
            echo "No states were selected";
        }
    }

    public static function allCities($val, $tbl) {
        try {

            $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE state_id = :std ORDER BY name ASC");
            $stmt->bindParam('std', $val, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            //echo $e->getMessage();
            echo "No cities were selected";
        }
    }
}
