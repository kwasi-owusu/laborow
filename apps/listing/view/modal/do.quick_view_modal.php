<?php
$asset_ID       = $_GET['asset_id'];
$rent_amount    = $_GET['rent_amount'];

require_once dirname(__DIR__, 2) . '/controller/CTRLFetchAll.php';
$asset_title = $_GET['asset_title'];
$fetch_image_for_modal = new FetchAllCTRL('assets', 'asset_images', 'asset_category', 'asset_sub_category');

$fetch_asset_images_for_modal       = $fetch_image_for_modal->fetchListingImagesCTRL($asset_ID);
$fetch_modal_image                  = $fetch_asset_images_for_modal->fetchAll();


?>
<div class="row">
    <div class="col-md-6 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
        <div class="detail-gallery">
        <span class="zoom-icon"><i class="fi-rs-search"></i></span>
            <!-- MAIN SLIDES -->
            <div class="product-image-slider">
                <figure class="border-radius-10">
                    <?php
                    foreach ($fetch_modal_image as $mg) {
                    ?>
                        <img class="default-img" src="<?php echo $mg['asset_image_save']; ?>" alt="" />
                    <?php
                    }
                    ?>
                </figure>

            </div>
            <!-- THUMBNAILS -->
            <div class="slider-nav-thumbnails">
                <div>
                    <?php
                    $thumbnail_instance         = new FetchAllCTRL('assets', 'asset_images', 'asset_category', 'asset_sub_category');
                    $thumbnails_images          = $thumbnail_instance->fetchListingImagesCTRL($asset_ID);
                    $loop_thumbnail             = $thumbnails_images->fetchAll();
                    
                    foreach ($loop_thumbnail as $mdg) {
                    ?>
                        <img src="<?php echo $mdg['asset_image_save']; ?>" alt="<?php echo $mdg['asset_image_ID']; ?>" />
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- End Gallery -->
    </div>
    <div class="col-md-6 col-sm-12 col-xs-12">
        <div class="detail-info pr-30 pl-30">
            <span class="stock-status out-stock"> Hot </span>
            <h3 class="title-detail">
                <a href="shop-product-right.html" class="text-heading">
                    <?php
                    echo $asset_title;

                    ?>
                </a>
            </h3>

            <div class="clearfix product-price-cover">
                <div class="product-price primary-color float-left">
                    <span class="current-price text-brand">GHS<?php echo $rent_amount; ?></span>
                </div>
            </div>
            <div class="detail-extralink mb-30">
                <div class="product-extra-link2">
                    <button type="submit" class="button button-add-to-cart"><i class="fi-rs-shopping-cart"></i>Rent Now</button>
                </div>
            </div>
        </div>
        <!-- Detail Info -->
    </div>
</div>