<header class="header-area header-area-one">
    <div class="header-top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3">
                    <div class="top-social">
                        <ul class="social-link">
                            <li><span>Follow us:</span></li>
                            <li><a href="#"><i class="ti-facebook"></i></a></li>
                            <li><a href="#"><i class="ti-twitter-alt"></i></a></li>
                            <li><a href="#"><i class="ti-pinterest"></i></a></li>
                            <li><a href="#"><i class="ti-youtube"></i></a></li>
                            <li><a href="#"><i class="ti-instagram"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="top-content text-center">
                        <p>Someone is interested in renting your parking space, ladder, wheelbarrow or your oven. <a href="listing">List them Now</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header-navigation">
        <div class="container-fluid">
            <div class="primary-menu">
                <div class="row">
                    <div class="col-lg-2 col-5">
                        <div class="site-branding">
                            <a href="index.html" class="brand-logo"><img src="assets/images/logo/logo-1.png" alt="Brand Logo"></a>
                        </div>
                    </div>
                    <div class="col-lg-6 col-2">
                        <div class="nav-menu">
                            <div class="navbar-close"><i class="ti-close"></i></div>
                            <nav class="main-menu">
                                <ul>
                                    <li class="menu-item"><a href="index.html" class="active">Home</a>
                                    </li>
                                    <li class="menu-item"><a href="about.html">About us</a></li>

                                    <li class="menu-item has-children"><a href="javascript:void(0);">Listings</a>
                                        <ul class="sub-menu">

                                            <?php
                                            $allListingCategories = CTRGetListingCategories::GetListingCategoriesCTR();
                                            foreach ($allListingCategories as $ctg) {
                                            ?>
                                                <li class="menu-item"><a href="<?php echo $ctg['category_slug']; ?>"><?php echo $ctg['category_desc']; ?></a>

                                            <?php
                                            }
                                            ?>

                                        </ul>
                                    </li>

                                    <li class="menu-item"><a href="how-it-works">How It Works</a></li>
                                    <li class="menu-item"><a href="contact">Contact</a></li>
                                    <li class="menu-item"><a href="Blog">Blog</a></li>
                                    
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="col-lg-4 col-5">
                        <div class="header-right-nav">
                            <ul class="d-flex align-items-center">
                                <li class="hero-nav-btn"><a href="add-listing.html" class="main-btn icon-btn">Add Listing</a></li>
                                <li class="nav-toggle-btn">
                                    <div class="navbar-toggler">
                                        <span></span><span></span><span></span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>