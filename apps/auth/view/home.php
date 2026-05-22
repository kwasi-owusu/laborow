<?php
require_once 'apps/template/home_temp.php';

require_once 'DoUserCors.php';
$page_name         = "User_login";
$token             = DoUserCors::loginCors($page_name);

$_SESSION['loginPage']  = $token;
?>
<div class="form-container">
        <div class="form-form">
            <div class="form-form-wrap">
                <div class="form-container">
                    <div class="form-content">

                        <h1 class="">Log In to <a href="index-2.html"><span class="brand-name">Rails ERP</span></a></h1>
                        <p class="signup">New Here? <a href="auth_register.html">Create an account</a></p>
                        <form class="text-left" id="login_user_frm" method="post" action="" autocomplete="off">
                            <div class="form">

                                <div id="username-field" class="field-wrapper input" style="border-radius:5px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <input id="lgnUser" name="lgnUser" type="email" class="form-control" placeholder="Username">
                                    <input type="hidden" name="lgn-tkn" value="<?php echo $token; ?>" required>
                                </div>

                                <div id="password-field" class="field-wrapper input mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <input id="password" name="lgnPwd" type="password" class="form-control" placeholder="Password">
                                </div>
                                <div class="d-sm-flex justify-content-between">
                                    <div class="field-wrapper toggle-pass">
                                        <p class="d-inline-block">Show Password</p>
                                        <label class="switch s-primary">
                                            <input type="checkbox" id="toggle-password" class="d-none">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="field-wrapper">
                                        <button type="submit" class="btn btn-primary" value="">Log In</button>
                                    </div>
                                    
                                </div>

                                <div class="col-12">

                                </div>
                                
                                <div class="col-12">
                                    <div class="loader multi-loader mx-auto" style="display: none;" id="loader"></div>
                                </div>

                                <div class="field-wrapper text-center keep-logged-in">
                                    <div class="n-chk new-checkbox checkbox-outline-primary">
                                        <label class="new-control new-checkbox checkbox-outline-primary">
                                          <input type="checkbox" class="new-control-input">
                                          <span class="new-control-indicator"></span>Keep me logged in
                                        </label>
                                    </div>
                                </div>

                                <div class="field-wrapper">
                                    <a href="auth_pass_recovery.html" class="forgot-pass-link">Forgot Password?</a>
                                </div>

                                <p id="responseHere"></p>

                            </div>
                        </form>   
                        
                        <p class="terms-conditions">
                            © <?php echo Date('Y'); ?>
                         All Rights Reserved. 
                         A Product of <a href="https://viobex.com">Viobex</a>
                         <hr />
                         <a href="viobu.com/privacy;">Privacy</a>, and <a href="viobu.com/terms">Terms</a>.</p>

                    </div>                    
                </div>
            </div>
        </div>
        <div class="form-image">
            <div class="l-image">
                <img src="apps/template/statics//assets/img/bg.webp" style="height: 100%;width:100%;" />
            </div>
        </div>
    </div>

    <?php
require_once 'apps/template/home_footer.php';
?>