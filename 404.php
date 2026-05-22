<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sorry! - Page No Found</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:title" content="" />
    <meta property="og:type" content="" />
    <meta property="og:url" content="" />
    <meta property="og:image" content="" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="apps/template/statics/assets/imgs/theme/logo.png" />
    <!-- Template CSS -->
    <link rel="stylesheet" href="apps/template/statics/assets/css/plugins/animate.min.css" />
    <link rel="stylesheet" href="apps/template/statics/assets/css/main17e6.css?v=5.2" />
</head>

<body>
    <?php
    require_once "apps/template/main_header_b.php";
    ?>

    <!--End header-->
    <main class="main page-404">
        <div class="page-content pt-150 pb-150">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 col-lg-10 col-md-12 m-auto text-center">
                        <p class="mb-20"><img src="assets/imgs/page/page-404.png" alt="" class="hover-up" /></p>
                        <h1 class="display-2 mb-30">Page Not Found</h1>
                        <p class="font-lg text-grey-700 mb-30">
                            The link you clicked may be broken or the page may have been removed.<br />
                            visit the <a href="home"> <span> Homepage</span></a> or <a href="contact"><span>Contact us</span></a> about the problem
                        </p>
                        <div class="search-form">
                            <form action="#">
                                <input type="text" placeholder="Search…" />
                                <button type="submit"><i class="fi-rs-search"></i></button>
                            </form>
                        </div>
                        <a class="btn btn-default submit-auto-width font-xs hover-up mt-30" href="home"><i class="fi-rs-home mr-5"></i>
                            Back To Home Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
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