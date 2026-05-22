<?php
require_once dirname(__DIR__, 2) . '/settings/model/MainMDLGetListingCategories.php';

class MainCTRLGEtListingCategories extends MainMDLGetListingCategories {

    public static function GetListingCategoriesCTR() {
        $tbl = 'asset_category';

        $getRst = MainMDLGetListingCategories::getListingCategoriesMDL($tbl);

        return $getRst;
    }

    public static function GetListingSubCategoriesCTR($asset_category_ID) {
        $tbl = 'asset_sub_category';

        $getRst = MainMDLGetListingCategories::getListingCategoriesByIDMDL($tbl, $asset_category_ID);

        return $getRst;
    }

    

}