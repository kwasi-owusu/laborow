<?php

require_once '../model/MDLGetListingSubCategory.php';

class CTRGetSubCategoryByID extends MDLGetListingSubCategory{

    public static function getSubCategoryCTR(){
        $asset_category_ID = trim($_POST['asset_category_id']);
        if ($asset_category_ID != "_9"){
            $tbl    = 'asset_sub_category';
                        
            $rqsModel = MDLGetListingSubCategory::getListingSubCategoriesMDL($tbl, $asset_category_ID) ;
            
            if (isset($rqsModel)) {
                foreach ($rqsModel as $sct) {
                    $subCat     = $sct['asset_sub_category_desc'];
                    $subCatID   = $sct['asset_sub_category_ID'];
                    echo "<option value='" . $subCatID . "'>$subCat</option>";

                }
            } else {

                echo "<option value='01'>No Sub Category Available</option>";

            }
        }
    }
}

CTRGetSubCategoryByID::getSubCategoryCTR();