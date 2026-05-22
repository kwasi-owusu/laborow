<?php
$page_title = "Login";
require_once dirname(__DIR__, 2) . '/template/head.php';

require_once dirname(__DIR__) . '/controller/AuthEnums.php';
require_once dirname(__DIR__) . '/controller/CTRLSecureLogin.php';


$page_name          = AuthEnums::login_page_name->value;
$hash_key           = AuthEnums::secure_form_cors_hash->value;


$create_token       = new CTRLSecureLogin();
$login_token        = $create_token->is_login_hash_valid($page_name, $hash_key);
$_SESSION['login_tkn'] = $login_token;


require_once dirname(__DIR__, 2) . '/settings/controller/MainCTRLGEtListingCategories.php';


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
                        <h5>Login to your account</h5>
                        <p></p>
                        <form id="login_user_frm" action="" method="post" autocomplete="off">
                            <div class="info">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <input type="hidden" class="form-control" required name="lgn-tkn" id="tkn" style="border: 1px solid #092;" value="<?php echo $login_token; ?>" />
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
                                        <label>Enter the Security Code <span class="required">*</span></label>
                                        <input required id="enter_sec_code" style="border: 1px solid #092;" onkeypress="return IsNumeric(event);" ondrop="return false;" class="form-control" name="enter_sec_code" type="text" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Security Code<span class="required">*</span></label>
                                        <input required id="sec_code" style="border: 1px solid #092; color:#092; font-weight:bold" class="form-control" readonly name="sec_code" type="text" />

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Login</button>
                                    </div>

                                </div>
                                <hr />

                                <div class="row">
                                    <div class="col-md-2">
                                        <span><a href="forgot-password">Forgot Password?</a></span>
                                    </div>

                                    <div class="col-md-4">
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

    <script>
        let chars = "0123456789";
        let string_length = 8;
        let sec_kode = "";
        for (let i = 0; i < string_length; i++) {
            let rnum = Math.floor(Math.random() * chars.length);
            sec_kode += chars.substring(rnum, rnum + 1);
        }
        document.getElementById("sec_code").value = sec_kode;
    </script>


</body>

</html>