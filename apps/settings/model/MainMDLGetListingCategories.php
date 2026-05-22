<?php
require_once '../../template/statics/conn/anthrax.php';

class MainMDLGetListingCategories{
    public static function getListingCategoriesMDL($tbl) { 
        $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl ORDER BY category_desc ASC");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function getListingCategoriesByIDMDL($tbl, $asset_category_ID) { 
        $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE asset_category_ID = :cds");
        $stmt->bindValue('cds', $asset_category_ID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public static function getListingSubCategoriesMDL($tbl, $asset_category_ID) {
        $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE asset_category_ID = :cds ORDER BY asset_sub_category_desc ASC");
        $stmt->bindValue('cds', $asset_category_ID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public static function getListingSubCategoriesByIDMDL($tbl, $asset_sub_category_ID) {
        $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE asset_sub_category_ID = :acd");
        $stmt->bindValue('acd', $asset_sub_category_ID, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }


}