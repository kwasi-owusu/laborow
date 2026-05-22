<?php
require_once dirname(__DIR__) . '/model/MDLGetListingCategories.php';


class CTRGetListingCategories extends MDLGetListingCategories {

    public static function GetListingCategoriesCTR() {
        $tbl = 'asset_category';

        $getRst = MDLGetListingCategories::getListingCategoriesMDL($tbl);

        return $getRst;
    }

    public static function GetListingSubCategoriesCTR($asset_category_ID) {
        $tbl = 'asset_sub_category';

        $getRst = MDLGetListingCategories::getListingCategoriesByIDMDL($tbl, $asset_category_ID);

        return $getRst;
    }

    public static function GetListingSubCategoriesBySlugCTR($category_slug) {
        
        $tbl = 'asset_category';

        $getRst = MDLGetListingCategories::getListingCategoriesBySlugMDL($tbl, $category_slug);

        return $getRst;
    }

    

}