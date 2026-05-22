<?php
if (!isset($_SESSION)) {
    session_start();
}

$page_title = "Signup";
require_once dirname(__DIR__, 2) . '/template/head.php';

require_once dirname(__DIR__, 2) . '/settings/controller/MainCTRLGEtListingCategories.php';

require_once dirname(__DIR__) . '/controller/AuthEnums.php';
require_once dirname(__DIR__) . '/controller/CTRLSecureLogin.php';

$page_name          = AuthEnums::login_page_name->value;
$hash_key           = AuthEnums::secure_form_cors_hash->value;


$create_token       = new CTRLSecureLogin();
$signup_token       = $create_token->is_login_hash_valid($page_name, $hash_key);
$_SESSION['addUserTkn'] = $signup_token;

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
                        <h5>Sign Up</h5>
                        <p></p>
                        <form id="user_sign_up_form" action="" method="post" autocomplete="off">
                            <div class="info">

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <input type="hidden" class="form-control" required name="tkn" id="tkn" style="border: 1px solid #092;" value="<?php echo $signup_token; ?>" />
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Enter Your First Name <span class="required">*</span></label>
                                        <input required id="fname" style="border: 1px solid #092;" class="form-control" name="fname" type="text" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Enter Your Last Name <span class="required">*</span></label>
                                        <input required id="lname" style="border: 1px solid #092;" class="form-control" name="lname" type="text" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Enter Your Email <span class="required">*</span></label>
                                        <input required id="email" style="border: 1px solid #092;" class="form-control" name="lgnUser" type="email" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Enter Your Password <span class="required">*</span></label>
                                        <input required id="password" style="border: 1px solid #092;" class="form-control" name="lgnPwd" type="password" />

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Select You Country <span class="required">*</span></label>
                                        <select class="form-control" required name="country" id="country" style="border: 1px solid #092;">
                                            <option value="_9">Select Country</option>
                                            <option value="Ghana">Ghana</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="Kenya">Kenya</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>State/Region <span class="required">*</span></label>
                                        <select class="form-control" required name="state_region" id="state_region" style="border: 1px solid #092;">
                                            <option value="_9">Select State/Region</option>
                                            <option value="Ghana">Ghana</option>
                                            <option value="Nigeria">Nigeria</option>
                                            <option value="Kenya">Kenya</option>
                                        </select>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Enter Your City/Town <span class="required">*</span></label>
                                        <input required id="city_town" style="border: 1px solid #092;" class="form-control" name="city_town" type="text" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Enter Your Phone Number <span class="required">*</span></label>
                                        <input required id="phone_number" style="border: 1px solid #092;" onkeypress="return IsNumeric(event);" ondrop="return false;" class="form-control" name="phone_number" type="text" />

                                    </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>User Type <span class="required">*</span></label>
                                        <div class="custome-radio">
                                            <input class="form-check-input" required type="radio" value="individual" name="user_type" id="exampleRadios3" checked />
                                            <label class="form-check-label" for="exampleRadios3" data-bs-toggle="collapse">I am an Individual</label>
                                            &nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
                                            <input class="form-check-input" required type="radio" value="business" name="user_type" id="exampleRadios4" />
                                            <label class="form-check-label" for="exampleRadios4" data-bs-toggle="collapse">I am a Company</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Register</button>
                                    </div>

                                </div>
                                <hr />

                                <div class="row">
                                    <div class="col-md-4">
                                        <span><a href="login">Login if you already have an account?</a></span>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="loader multi-loader mx-auto" style="display: none;" id="loader"></div>
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

                        <p id="response_here"></p>

                    </div>
                </div>

            </div>
        </section>
    </main>

    <?php
    require_once dirname(__DIR__, 2) . '/template/footer.php';
    ?>
    <!--====== back-to-top ======-->
    <a href="#" class="back-to-top"><i class="ti-angle-up"></i></a>
    <!--====== Jquery js ======-->


    <?php
    require_once dirname(__DIR__, 2) . '/template/footer_script.php';
    ?>

    <script src="apps/auth/js/extra.js"></script>
    <script src="apps/settings/js/extra.js"></script>


</body>

</html>