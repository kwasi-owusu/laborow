<?php

require_once dirname(__DIR__, 2) . '/listing/controller/CTRLFetchAll.php';

$page_title = "Welcome to the future of renting";

require_once dirname(__DIR__, 2) . '/template/head.php';


?>

<body>
    <!-- Modal -->
    <div class="modal fade custom-modal" id="onloadModal" tabindex="-1" aria-labelledby="onloadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="deal" style="background-image: url('apps/template/statics/assets/imgs/banner/popup-2.png')">
                        <div class="deal-content detail-info">
                            <h4 class="product-title">
                                Hey! Welcome to Laborow.
                            </h4>
                            <div class="clearfix product-price-cover">
                                <div class="product-price primary-color float-left">
                                    <span class="current-price text-brand">Here's How <br /> I work:</span>
                                </div>
                            </div>
                        </div>

                        <div class="deal-bottom">
                            <p class="mb-20">
                                Renting is my business.
                            </p>

                            <hr style="width: 50%; color: #E31D1D;" />

                            <p class="mb-20">
                                I connect individuals and businesses in need of anything rentable <br />
                                with those who have them to rent.
                            </p>

                            <p class="mb-20" style="color: #092;">
                                List your assets and make some money from renting them out.
                            </p>

                            <p class="mb-20">
                                List your ladder, wheelbarrow, lawn mower, forklift, <br />
                                concrete mixers, excavators, game consoles, tents, canopies, <br />
                                spare rooms, parking spaces, sound systems, billboards, and more.
                            </p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Quick view -->
    <div class="modal fade custom-modal" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 col-xs-12 mb-md-0 mb-sm-5">
                            <div class="detail-gallery">
                                <span class="zoom-icon"><i class="fi-rs-search"></i></span>
                                <!-- MAIN SLIDES -->
                                <div class="product-image-slider">
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-2.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-1.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-3.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-4.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-5.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-6.jpg" alt="product image" />
                                    </figure>
                                    <figure class="border-radius-10">
                                        <img src="apps/template/statics/assets/imgs/shop/product-16-7.jpg" alt="product image" />
                                    </figure>
                                </div>
                                <!-- THUMBNAILS -->
                                <div class="slider-nav-thumbnails">
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-3.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-4.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-5.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-6.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-7.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-8.jpg" alt="product image" /></div>
                                    <div><img src="apps/template/statics/assets/imgs/shop/thumbnail-9.jpg" alt="product image" /></div>
                                </div>
                            </div>
                            <!-- End Gallery -->
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="detail-info pr-30 pl-30">
                                <span class="stock-status out-stock"> Sale Off </span>
                                <h3 class="title-detail"><a href="shop-product-right.html" class="text-heading">Seeds of Change Organic Quinoa, Brown</a></h3>
                                <div class="product-detail-rating">
                                    <div class="product-rate-cover text-end">
                                        <div class="product-rate d-inline-block">
                                            <div class="product-rating" style="width: 90%"></div>
                                        </div>
                                        <span class="font-small ml-5 text-muted"> (32 reviews)</span>
                                    </div>
                                </div>
                                <div class="clearfix product-price-cover">
                                    <div class="product-price primary-color float-left">
                                        <span class="current-price text-brand">$38</span>
                                        <span>
                                            <span class="save-price font-md color3 ml-15">26% Off</span>
                                            <span class="old-price font-md ml-15">$52</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="detail-extralink mb-30">
                                    <div class="detail-qty border radius">
                                        <a href="#" class="qty-down"><i class="fi-rs-angle-small-down"></i></a>
                                        <span class="qty-val">1</span>
                                        <a href="#" class="qty-up"><i class="fi-rs-angle-small-up"></i></a>
                                    </div>
                                    <div class="product-extra-link2">
                                        <button type="submit" class="button button-add-to-cart"><i class="fi-rs-shopping-cart"></i>Add to cart</button>
                                    </div>
                                </div>
                                <div class="font-xs">
                                    <ul>
                                        <li class="mb-5">Vendor: <span class="text-brand">Nest</span></li>
                                        <li class="mb-5">MFG:<span class="text-brand"> Jun 4.2022</span></li>
                                    </ul>
                                </div>
                            </div>
                            <!-- Detail Info -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once dirname(__DIR__) . '/main_header_b.php';
    ?>

    <!--End header-->
    <main class="main">

    <section class="banners mb-25">
            <div class="container">
                <div class="row">
                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow fadeIn animated" style="background:#0d6efd; color:#fff;">
                            <div class="banner-icon">
                                <img src="apps/template/statics/assets/imgs/theme/icons/icon-1.svg" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title" style="color: #fff;">Rent Anything</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow fadeIn animated" style="background:#0d6efd; color:#fff;">
                            <div class="banner-icon">
                                <img src="apps/template/statics/assets/imgs/theme/icons/icon-6.svg" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title" style="color: #fff;">Speedy Delivery</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow fadeIn animated" style="background:#0d6efd; color:#fff;">
                            <div class="banner-icon">
                                <img src="apps/template/statics/assets/imgs/theme/icons/icon-3.svg" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title" style="color: #fff;">Lowest Fee</h3>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow fadeIn animated" style="background:#0d6efd; color:#fff;">
                            <div class="banner-icon">
                            <img src="apps/template/statics/assets/imgs/theme/icons/icon-2.svg" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title" style="color: #fff;">Friendly Service</h3>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-1-5 col-md-4 col-12 col-sm-6">
                        <div class="banner-left-icon d-flex align-items-center wow fadeIn animated" style="background:#0d6efd; color:#fff;">
                            <div class="banner-icon">
                                <img src="apps/template/statics/assets/imgs/theme/icons/icon-5.svg" alt="" />
                            </div>
                            <div class="banner-text">
                                <h3 class="icon-box-title" style="color: #fff;">Localized Service</h3>
                            </div>
                        </div>

                    </div>

                </div>
        </section>
        <!--End category slider-->
        <section class="banners mb-25">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <img src="apps/template/statics/assets/imgs/popular/ads.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Webpage <br />Rent A Billboard<br />
                                    Car Ads Space
                                </h4>
                                <a href="rent/advertising-space" class="btn btn-xs">More Advertising Space <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <img src="apps/template/statics/assets/imgs/popular/Automobile.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Truck<br />
                                    An RV <br />
                                    A Van
                                </h4>
                                <a href="rent/automobile" class="btn btn-xs">More on Automobile <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-md-none d-lg-flex">
                        <div class="banner-img mb-sm-0 wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <img src="apps/template/statics/assets/imgs/popular/electronics.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Digital Camera <br />
                                    Laptop Battery <br />
                                    TV for an Event
                                </h4>
                                <a href="rent/electronics" class="btn btn-xs">More Electronics <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <img src="apps/template/statics/assets/imgs/popular/events.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent Sounds System <br />
                                    Event Center<br />
                                    Tents and Canopies
                                </h4>
                                <a href="rent/events-management" class="btn btn-xs">More Event Management <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <img src="apps/template/statics/assets/imgs/popular/farm.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Wheelbarrow<br />
                                    Spraying Machine <br />
                                    Watering Can

                                </h4>
                                <a href="rent/farm-tools" class="btn btn-xs">More Farm Tools <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-md-none d-lg-flex">
                        <div class="banner-img mb-sm-0 wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <img src="apps/template/statics/assets/imgs/popular/fishing.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Fishing Net <br />
                                    Fishing Rod
                                </h4>
                                <a href="rent/fishing-tools" class="btn btn-xs">More Fishing Tools<i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay="0">
                            <img src="apps/template/statics/assets/imgs/popular/properties.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Parking Space <br />
                                    Spare Room <br />
                                    Storage Space
                                </h4>
                                <a href="rent/properties" class="btn btn-xs">More Properties <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="banner-img wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                            <img src="apps/template/statics/assets/imgs/popular/sports.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent Table Tennis Bats<br />
                                    Game Consoles <br />
                                    Skates
                                </h4>
                                <a href="rent/sporting-items" class="btn btn-xs">More Sporting Items <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 d-md-none d-lg-flex">
                        <div class="banner-img mb-sm-0 wow animate__animated animate__fadeInUp" data-wow-delay=".4s">
                            <img src="apps/template/statics/assets/imgs/popular/mower.png" alt="" />
                            <div class="banner-text">
                                <h4>
                                    Rent a Lawn Mower <br />
                                    Rent a Ladder <br />
                                    Rent a Sewing Machine <br />
                                </h4>
                                <a href="rent/home-tools" class="btn btn-xs">More Home Tools <i class="fi-rs-arrow-small-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </section>

        
        <!--End banners-->
    </main>

    <!-- Preloader Start -->
    <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="text-center">
                    <img src="apps/template/statics/assets/imgs/theme/logo.png" alt="" />
                </div>
            </div>
        </div>
    </div>
    <!-- Vendor JS-->

    <?php
    require_once "apps/template/footer.php";
    ?>
    <!--====== back-to-top ======-->
    <a href="#" class="back-to-top"><i class="ti-angle-up"></i></a>
    <!--====== Jquery js ======-->

    <?php
    require_once "apps/template/footer_script.php";
    ?>

    <script src="apps/listing/js/extra.js"></script>
</body>


</html>