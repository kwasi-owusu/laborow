<?php

require_once '../../template/statics/conn/anthrax.php';

class MDLGetListingSubCategory{

    public static function getListingSubCategoriesMDL($tbl, $asset_category_ID) {
        $stmt =  Connection::connect()->prepare("SELECT * FROM $tbl WHERE asset_category_ID = :cds");
        $stmt->bindParam('cds', $asset_category_ID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

}