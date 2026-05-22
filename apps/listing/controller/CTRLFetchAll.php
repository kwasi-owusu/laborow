<?php

require_once dirname(__DIR__) . '/model/MDLFetchListing.php';

class FetchAllCTRL
{

    private $tbl            = 'assets';
    private $tbl_b          = 'asset_images';
    private $tbl_c          = 'asset_category';
    private $tbl_d          = 'asset_sub_category';

    //private $db;

    function __construct($tbl, $tbl_b, $tbl_c, $tbl_d)
    {

        $this->tbl      = $tbl;
        $this->tbl_b    = $tbl_b;
        $this->tbl_c    = $tbl_c;
        $this->tbl_d    = $tbl_d;
    }

    public function fetchLatestListingCTRL(MDLFetchListing $ft)
    {

        $fetchListing = new $ft();

        $getRst = $fetchListing->fetchListingMDL($this->tbl, $this->tbl_b, $this->tbl_c, $this->tbl_d);

        return $getRst;
    }



    public function fetchListingByCategoryCTRL($asset_category_ID): array
    {
        $fetchListing = new MDLFetchListing();
        return $fetchListing->fetchListingByCategoryMDL($asset_category_ID);
    }


    public function fetchListingImagesCTRL($asset_ID)
    {

        $fetchListing = new MDLFetchListing();

        $getRst = $fetchListing->fetchListingImagesMDL($this->tbl_b, $asset_ID);

        return $getRst;
    }

    public function fetchListingByThisSlugCTRL($item_slug)
    {

        $fetchListing = new MDLFetchListing();

        $data = array(
            'slg' => $item_slug
        );

        $getRst = $fetchListing->fetchSingleListingBySlugMDL($this->tbl, $this->tbl_b, $this->tbl_c, $this->tbl_d, $data);

        return $getRst;
    }
}
