<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class MDLoadMoreListings
{
    public function LoadMoreListingsMDL($table_a, $table_b, $data)
    //$offset, $no_of_records_per_page, $prop_type, $prop_sub_type
    {
        $newPDO = new Connection();
        $thisPDO = $newPDO->Connect();
       
        try {
            $stmt = $thisPDO->prepare("SELECT $table_a.*, $table_b.* FROM $table_a 
            INNER JOIN $table_b ON $table_b.asset_ID = $table_a.assets_ID
            WHERE $table_a.category_ID  = :ct
            
            LIMIT :offset, :no_of_records_per_page");
            $stmt->bindParam('ct', $data['listing_category'], PDO::PARAM_STR);
            $stmt->bindParam('offset', $data['offset'], PDO::PARAM_STR);
            $stmt->bindParam('no_of_records_per_page', $data['no_of_records_per_page'], PDO::PARAM_STR);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}
