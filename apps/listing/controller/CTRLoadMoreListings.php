<?php

class CTRLoadMoreListings{

    private string $table_a;
    private string $table_b;

    public function __construct($table_a, $table_b)
    {
        $this->table_a = $table_a;
        $this->table_b = $table_b;
    }
 
    public function LoadMoreListingsCTRL(){

        require_once dirname(__DIR__) . '/model/MDLoadMoreListings.php';
        
        $instance_of_listing = new MDLoadMoreListings();

        $pgno                   = trim(strip_tags($_POST['nxtpg']));
        $listing_category       = trim(strip_tags($_POST['listing_category']));
        

        $no_of_records_per_page = 4;
        $offset = ($pgno - 1) * $no_of_records_per_page;

        $data = array(
            'offset' => $offset,
            'no_of_records_per_page' => $no_of_records_per_page,
            'listing_category' => $listing_category
        );
        

        $qry = $instance_of_listing->LoadMoreListingsMDL($this->$table_a, $this->$table_b, $data);
        
        $this_listing = $qry->fetchAll();


        foreach ($this_listing as $apt) {
            #### images ##########
            $prop_ID = $lst['props_ID'];
            $imgs = $prp->propimages($prop_ID);
            $c_img = $imgs->fetch(PDO::FETCH_ASSOC);

            $apt_sub = $lst['prop_sub_type'];

            $apt_for = '';


            ///check property for
            if ($apt_sub == 1) {
                $apt_for = "Sale";
            } elseif ($apt_sub == 2) {
                $apt_for = "Rent";
            } elseif ($apt_sub == 3) {
                $apt_for = "Lease";
            }
            ?>
            <div class="listing-item">
                <a href="<?php echo $apt['title_slug']; ?>" class="listing-img-container">
                    <div class="listing-badges">

                    </div>
                    <div class="listing-img-content">
                        <span class="listing-price"><?php echo $apt['prop_currency'] . number_format($apt['prop_price'], 2); ?></span>
                    </div>
                    <div class="listing-carousel">
                        <div>
                            <img src="blade/controller/<?php echo $c_img['img_nm']; ?>" alt="">
                        </div>
                    </div>
                </a>
                <div class="listing-content">
                    <div class="listing-title">
                        <h4><a href="<?php echo $apt['title_slug']; ?>"><?php echo $apt['prop_title']; ?></a>
                        </h4>
                        <i class="fa fa-map-marker"></i>
                        <?php echo $apt['prop_location']; ?>
                        <a href="<?php echo $apt['title_slug']; ?>" class="details button border">Details</a>
                    </div>
                </div>
            </div>
            <?php
        }

    }
}

$callClass = new CTRLoadMoreListings('assets', 'asset_images');
$callMethod = $callClass->LoadMoreListingsCTRL();
