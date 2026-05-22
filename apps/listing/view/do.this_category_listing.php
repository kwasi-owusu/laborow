<?php

require_once dirname(__DIR__, 2) . '/settings/controller/CTRGetListingCategories.php';
require_once dirname(__DIR__) . '/controller/CTRLFetchAll.php';

$category_slug              = trim($_REQUEST['cat_slg']);

$callCategoryBySlugMethod   = CTRGetListingCategories::GetListingSubCategoriesBySlugCTR($category_slug);
$fetchCatSlg                = $callCategoryBySlugMethod->fetch(PDO::FETCH_ASSOC);
$asset_category_ID          = isset($fetchCatSlg['asset_category_ID']) ? $fetchCatSlg['asset_category_ID'] : null;
$category_desc              = isset($fetchCatSlg['category_desc']) ? $fetchCatSlg['category_desc'] : null;

//get the listing based on the category id
$callCategoryByIDMethod     = CTRGetListingCategories::GetListingSubCategoriesCTR($asset_category_ID);

$page_title                 = "Rent " . $category_desc;

require_once dirname(__DIR__, 2) . '/template/other_head.php';



$callBySubCategoryMethod        = CTRGetListingCategories::GetListingSubCategoriesCTR($asset_category_ID);
$cntAllSubCategory              = $callBySubCategoryMethod->rowCount();

//get assets based on this category

$instances_to_fetch_category    = new FetchAllCTRL('assets', 'asset_images', 'asset_category', 'asset_sub_category');
$instance_to_fetch_image        = new FetchAllCTRL('assets', 'asset_images', 'asset_category', 'asset_sub_category');
$fetchAssetsByCategories        = $instances_to_fetch_category->fetchListingByCategoryCTRL($asset_category_ID);

// $cntAssetByCategory             = $fetchAssetsByCategories->rowCount();
$cntAssetByCategory = count($fetchAssetsByCategories);

$_SESSION['category_desc'] =  $category_desc;

?>

<body>

    <?php

    //require_once dirname(__DIR__) . '/main_header_b.php';
    require_once dirname(__DIR__, 2) . '/template/main_header_b.php';
    ?>

    <!--End header-->
    <main class="main">
        <div class="page-header mt-30 mb-50">
            <div class="container">
                <div class="archive-header">
                    <div class="row align-items-center">
                        <div class="col-xl-8">
                            <h1 class="mb-15"><?php echo $category_desc; ?></h1>
                            <div class="breadcrumb">
                                <a href="home" rel="nofollow"><i class="fi-rs-home mr-5"></i>Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mb-30">
            <div class="row">
                <div class="col-lg-4-5">

                    <div class="row product-grid">

                        <?php
                        if ($cntAssetByCategory > 0) {
                            $fetchAssets = $fetchAssetsByCategories->fetchAll();
                            foreach ($fetchAssets as $asset) {

                                isset($asset['assets_ID']) ? $asset_ID = $asset['assets_ID'] : $asset_ID = null;
                                $fetch_asset_images     = $instance_to_fetch_image->fetchListingImagesCTRL($asset_ID);
                                $fetch_image            = $fetch_asset_images->fetch(PDO::FETCH_ASSOC);
                                $this_asset_image       = $fetch_image['asset_image_save'];
                        ?>
                                <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                                    <div class="product-cart-wrap mb-30">
                                        <div class="product-img-action-wrap">
                                            <div class="product-img product-img-zoom">
                                                <a href="item/<?php echo $asset['asset_slug']; ?>">

                                                    <img class="default-img" src="<?php echo $this_asset_image; ?>" alt="" />
                                                    <img class="hover-img" src="<?php echo $this_asset_image; ?>" alt="" />
                                                </a>
                                            </div>
                                            <div class="product-action-1">
                                                <a aria-label="Rent Later" class="action-btn" href="javascript:void(0);"><i class="fi-rs-heart"></i></a>
                                                <a aria-label="Quick view" class="action-btn" href="item/<?php echo $asset['asset_slug']; ?>" onclick="quickView(this)" data-id="<?php echo $asset['assets_ID']; ?>" data-title="<?php echo $asset['asset_title']; ?>" data-amount="<?php echo $asset['rent_amount']; ?>" data-desc="<?php echo $asset['asset_description']; ?>" data-slug="<?php echo $asset['asset_slug']; ?>">
                                                    <i class="fi-rs-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="product-content-wrap">
                                            <div class="product-category">
                                                <a href="shop-grid-right.html"><?php echo $category_desc; ?></a>
                                            </div>
                                            <h2><a href="shop-product-right.html"><?php echo $asset['asset_title']; ?></a></h2>

                                            <div>
                                                <span style="font-weight:bold;">Is Asset Available?
                                                    <?php echo $asset['asset_is_available']; ?>
                                                </span>
                                            </div>
                                            <div class="product-card-bottom">
                                                <div class="product-price">
                                                    <span>GHS<?php echo number_format($asset['rent_amount'], 2); ?></span>

                                                </div>
                                                <div class="add-cart">
                                                    <a class="add" href="item/<?php echo $asset['asset_slug']; ?>"><i class="fi-rs-shopping-cart mr-5"></i>Rent</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <?php
                            }
                        }
                        ?>
                        <!--end product card-->

                    </div>
                    <input type="hidden" id="pageno" value="1">
                    <input type="hidden" id="asset_category_ID" value="<?php echo $asset_category_ID; ?>">

                    <div class="col-md--12">
                        <div class="loader multi-loader mx-auto" style="display: none;" id="loader"></div>
                    </div>

                    <!--<img src="blade/view/assets/images/loader.gif" id="gif_loader">-->

                </div>
                <div class="col-lg-1-5 primary-sidebar sticky-sidebar">
                    <div class="sidebar-widget widget-category-2 mb-30">
                        <h5 class="section-title style-1 mb-30">More <?php echo $category_desc; ?></h5>
                        <ul>
                            <?php
                            if ($cntAllSubCategory > 0) {
                                $fetchBySubCat = $callBySubCategoryMethod->fetchAll();
                                foreach ($fetchBySubCat as $sbc) {
                            ?>
                                    <li>
                                        <a href="<?php echo $category_slug . '/' . $sbc['sub_category_slug']; ?>">
                                            <img src="apps/template/statics/assets/imgs/theme/icons/coupon.png" alt="" />
                                            <?php echo $sbc['asset_sub_category_desc']; ?>
                                        </a><span class="count">30</span>
                                    </li>
                                <?php
                                }
                            } else {
                                ?>
                                <li>
                                    <a href="shop-grid-right.html">
                                        <img src="apps/template/statics/assets/imgs/theme/icons/coupon.png" alt="" />
                                        No Sub Category Found
                                    </a><span class="count">30</span>
                                </li>
                            <?php
                            }
                            ?>

                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <?php
    require_once dirname(__DIR__, 2) . '/template/footer.php';
    ?>

    <!-- Preloader Start -->
    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="text-center">
                    <img src="assets/imgs/theme/loading.gif" alt="" />
                </div>
            </div>
        </div>
    </div>
    <!-- Vendor JS-->
    <!--====== back-to-top ======-->
    <a href="#" class="back-to-top"><i class="ti-angle-up"></i></a>
    <!--====== Jquery js ======-->

    <?php
    require_once dirname(__DIR__, 2) . '/template/footer_script.php';
    ?>

    <script src="apps/listing/js/extra.js"></script>
</body>

</html>