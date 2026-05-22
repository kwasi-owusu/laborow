<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';
class MDLAssetCategory
{

    public static function get_assets_categoriesMDL()
    {
        $stmt =  Connection::connect()->prepare("SELECT asset_category_ID, category_desc  
        FROM asset_category ORDER BY category_desc ASC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function get_assets_subCategoriesMDL($asset_category_ID)
    {
        $stmt =  Connection::connect()->prepare("SELECT asset_sub_category_ID, asset_category_ID, asset_sub_category_desc 
        FROM asset_sub_category WHERE asset_category_ID = :ct");
        $stmt->bindParam('ct', $asset_category_ID, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
