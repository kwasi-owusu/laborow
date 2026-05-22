
<?php
if (!isset($_SESSION)) {
    session_start();
}


require_once 'DoUserCors.php';
$page_name         = "do.login";
$token             = DoUserCors::loginCors($page_name);
$_SESSION['loginPage']  = $token;

$page_title = "Forgot Password";

require_once dirname(__DIR__, 2) . '/settings/controller/MainCTRLGEtListingCategories.php';

require_once dirname(__DIR__, 2) . '/template/head.php';
?>

<body>
    <?php
    require_once dirname(__DIR__, 2) . '/template/other_header.php';

    ?>

    <!--End header-->
    <main class="main">
        <section class="banners mb-25">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-sm-12 offset-md-2">
                        <?php
                        echo isset($_SESSION["isLogin"]) ? "<h5><a href='dashboard.php'>My Account</a></h5>" : "";
                        ?>
                        <hr />
                        <h5>Password reset request</h5>
                        <p></p>
                        <form id="login_user_frm" action="" method="post" autocomplete="off">
                            <div class="info">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <input type="hidden" class="form-control" required name="lgn-tkn" id="tkn" style="border: 1px solid #092;" value="<?php echo $token; ?>" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Enter your email address, if an account exist, you'll be emailed a link to reset your password. <span class="required">*</span></label>
                                        <input required id="email" style="border: 1px solid #092;" class="form-control" name="lgnUser" type="email" />
                                    </div>


                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Request</button>
                                    </div>

                                    <div class="col-md-1">
                                        <span><a href="login">Login</a></span>
                                    </div>

                                    <div class="col-md-2">
                                        <span><a href="signup">New User, Signup Here?</a></span>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="loader multi-loader mx-auto" style="display: none;" id="loader"></div>
                                    </div>
                                </div>


                                <p id="response_here"></p>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </section>
    </main>

    <!-- Preloader Start -->
    <!-- <div id="preloader-active">
        <div class="preloader d-flex align-items-center justify-content-center">
            <div class="preloader-inner position-relative">
                <div class="text-center">
                    <img src="assets/imgs/theme/loading.gif" alt="" />
                </div>
            </div>
        </div>
    </div> -->
    <!-- Vendor JS-->
    <?php
    require_once "../../template/footer.php";
    ?>
    <!--====== back-to-top ======-->
    <a href="#" class="back-to-top"><i class="ti-angle-up"></i></a>
    <!--====== Jquery js ======-->


    <?php
    require_once "../../template/footer_script.php";
    ?>

    <script src="apps/template/statics/toast/js/jquery.toast.js"></script>

    <script src="apps/auth/js/extra.js"></script>
    <script src="apps/settings/js/extra.js"></script>


</body>

</html>