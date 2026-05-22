<header class="header-area header-style-1 header-height-2">
    <div class="header-top header-top-ptb-1 d-none d-lg-block">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="text-center">
                        <div id="news-flash" class="d-inline-block">
                            <ul>
                                <li>185 people recently search for a wheelbarrow to rent</li>
                                <li>23 people recently rented a ladder</li>
                                <li>30 people are recently searched for an oven to rent</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <?php
                    echo isset($_SESSION["isLogin"]) ? "<h5><a href='account'>My Account</a></h5>" : "<h5><a href='login'>Login/Signup</a></h5>";
                    ?>
                </div>

            </div>
        </div>
    </div>
    <div class="header-middle header-middle-ptb-1 d-none d-lg-block">
        <div class="container">
            <div class="header-wrap row">
                <div class="logo logo-width-1 col.lg-2 col-md-2">
                    <a href="home"><img src="apps/template/statics/assets/imgs/theme/logo.png" alt="logo" /></a>
                </div>
                <div class="header-left col-lg-6 col-md-6">
                    <div class="search-style-2">
                        <div class="row align-items-center">

                            <form method="post" action="" id="search_by_category_id_frm">

                                <div class="form-group row">
                                    <div class="col-xl-5 col-md-5 col-lg-5">
                                        <select class="form-control" required name="asset_category" id="asset_category">
                                            <option value="_9">Select a Category</option>
                                            <optgroup label="All Categories">
                                                <?php
                                                $allListingCategories = MainCTRLGEtListingCategories::GetListingCategoriesCTR();

                                                foreach ($allListingCategories as $sct) {
                                                ?>
                                                    <option value="<?php echo $sct['asset_category_ID']; ?>"><?php echo $sct['category_desc']; ?></option>
                                                <?php
                                                }
                                                ?>

                                            </optgroup>

                                        </select>

                                    </div>

                                    <div class="col-md-5 col-lg-5 col-xl-5">

                                        <select class="form-control" required name="asset_sub_category" id="asset_sub_category">
                                            <option value="_9">Select a Subcategory</option>
                                            <optgroup label="Choose Sub Category" id="sub_cat_here">

                                            </optgroup>
                                        </select>

                                    </div>

                                    <div class="col-xl-4 col-lg-2">
                                        <button type="submit" class="btn" style="color: #fff;">Search</button>
                                    </div>

                                </div>

                            </form>

                        </div>
                    </div>

                </div>
                <div class="col-lg-2 col-md-2">
                    <a href="list" type="button" class="btn" style="color: #fff; height: 60px;">Add Your Listing</a>
                </div>
            </div>
        </div>
    </div>
    <div class="header-bottom header-bottom-bg-color sticky-bar">
        <div class="container">
            <div class="header-wrap header-space-between position-relative">
                <div class="logo logo-width-1 d-block d-lg-none">
                    <a href="home"><img src="apps/template/statics/assets/imgs/theme/logo.png" alt="logo" /></a>
                </div>
                <div class="header-nav d-none d-lg-flex">

                    <div class="main-menu main-menu-padding-1 main-menu-lh-2 d-none d-lg-block font-heading">
                        <nav>
                            <ul>
                                <li>
                                    <a href="home">Home</a>
                                </li>
                                <li>
                                    <a href="about">About</a>
                                </li>

                                <li>
                                    <a href="how-it-works">How it Works</a>
                                </li>

                                <li>
                                    <a class="active" href="javascript:void(0)">Find to Rent <i class="fi-rs-angle-down"></i></a>
                                    <ul class="sub-menu">
                                        <?php
                                        $allListingCategories = MainCTRLGEtListingCategories::GetListingCategoriesCTR();
                                        foreach ($allListingCategories as $ctg) {
                                        ?>
                                            <li><a href="rent/<?php echo $ctg['category_slug']; ?>"><?php echo $ctg['category_desc']; ?></a>

                                            <?php
                                        }
                                            ?>
                                    </ul>
                                </li>

                                <li>
                                    <a href="tips">How To</a>
                                </li>

                                <li>
                                    <a href="blog">Blog</a>
                                </li>

                                <li>
                                    <a href="contact">Contact</a>
                                </li>

                                <li>
                                    <?php
                                    echo isset($_SESSION["isLogin"]) ? "<a href='account'>My Account</a>" : "<a href='login'>Login</a>";
                                    ?>
                                </li>

                                <li>
                                <a href="invest">Invest</a>
                                </li>

                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="header-action-icon-2 d-block d-lg-none">
                    <div class="burger-icon burger-icon-white">
                        <span class="burger-icon-top"></span>
                        <span class="burger-icon-mid"></span>
                        <span class="burger-icon-bottom"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="mobile-header-active mobile-header-wrapper-style">
    <div class="mobile-header-wrapper-inner">
        <div class="mobile-header-top">
            <div class="mobile-header-logo">
                <a href="home"><img src="apps/template/statics/assets/imgs/theme/logo.png" alt="logo" /></a>
            </div>
            <div class="mobile-menu-close close-style-wrap close-style-position-inherit">
                <button class="close-style search-close">
                    <i class="icon-top"></i>
                    <i class="icon-bottom"></i>
                </button>
            </div>
        </div>
        <div class="mobile-header-content-area">
            <div class="mobile-menu-wrap mobile-header-border">
                <!-- mobile menu start -->
                <nav>
                    <ul class="mobile-menu font-heading">
                        <li>
                            <a href="home">Home</a>
                        </li>

                        <li>
                            <a href="about">About</a>
                        </li>

                        <li>
                            <a href="how-it-works">How it Works</a>
                        </li>

                        <li>
                            <a href="how-it-works">How it Works</a>
                        </li>

                        <li class="menu-item-has-children">
                            <a href="categories">Listings</a>
                            <ul class="dropdown">
                                <?php
                                $allListingCategories = MainCTRLGEtListingCategories::GetListingCategoriesCTR();
                                foreach ($allListingCategories as $ctg) {
                                ?>
                                    <li><a href="<?php echo $ctg['category_slug']; ?>"><?php echo $ctg['category_desc']; ?></a>

                                    <?php
                                }
                                    ?>
                            </ul>
                        </li>

                        <li>
                            <a href="tips">How To</a>
                        </li>

                        <li>
                            <a href="blog">Blog</a>
                        </li>

                        <li>
                            <a href="contact">Contact</a>
                        </li>

                        <li>
                                    <?php
                                    echo isset($_SESSION["isLogin"]) ? "<a href='account'>My Account</a>" : "<a href='login'>Login</a>";
                                    ?>
                                </li>

                                <li>
                                <a href="invest">Invest</a>
                                </li>
                    </ul>
                </nav>
                <!-- mobile menu end -->
            </div>
            <div class="mobile-header-info-wrap">
                <div class="single-mobile-header-info">
                    <a href="login"><i class="fi-rs-user"></i>Log In / Sign Up </a>
                </div>
            </div>
            <div class="mobile-social-icon mb-50">
                <h6 class="mb-15">Follow Us</h6>
                <a href="#"><img src="apps/template/statics/assets/imgs/theme/icons/icon-facebook-white.svg" alt="" /></a>
                <a href="#"><img src="apps/template/statics/assets/imgs/theme/icons/icon-twitter-white.svg" alt="" /></a>
                <a href="#"><img src="apps/template/statics/assets/imgs/theme/icons/icon-instagram-white.svg" alt="" /></a>
                <a href="#"><img src="apps/template/statics/assets/imgs/theme/icons/icon-pinterest-white.svg" alt="" /></a>
                <a href="#"><img src="apps/template/statics/assets/imgs/theme/icons/icon-youtube-white.svg" alt="" /></a>
            </div>
            <div class="site-copyright">Copyright <?php echo Date('Y'); ?> © Viobex.</div>
        </div>
    </div>
</div>