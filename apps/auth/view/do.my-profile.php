<?php
require_once '../../template/index.php';

require_once 'DoUserCors.php';
$page_name         = "edit_my_account";
$editUserToken     = DoUserCors::loginCors($page_name);
$_SESSION['editUserToken'] = $editUserToken;

$user_ID    = $_SESSION['uid'];

require_once('../controller/LoadMyAccount.php');
$getUser        = LoadMyAccount::fetchMyAccount($user_ID);
$callUser       = $getUser->fetch(PDO::FETCH_ASSOC);

?>

<div id="content" class="main-content">
    <div class="layout-px-spacing">

        <div class="row layout-top-spacing">

            <div class="col-xl-8 col-lg-8 col-sm-8 offset-2  layout-spacing">
                <div class="widget-content widget-content-area br-6">
                    <div class="w-content">
                        <span class="w-value" style="padding: 15px; font-size:20px; font-weight:bolder;">My Account</span>
                    </div>
                    <form id="update_my_password_frm" class="section work-experience" action="" method="post" autocomplete="off">
                        <div class="info">
                            <h5 class="">Edit User Profile</h5>
                            <div class="row">
                                <div class="col-md-11 mx-auto">

                                    <div class="work-section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="degree2">First Name</label>
                                                    <input type="text" class="form-control mb-4" id="first_name" name="fname" placeholder="User First Name" value="<?php echo $callUser['firstName'] ?>" required readonly>
                                                    <input type="hidden" class="form-control mb-4" id="tkn" readonly name="tkn" value="<?php echo $editUserToken; ?>" required>
                                                    <input type="hidden" class="form-control mb-4" id="user_ID" readonly name="user_ID" value="<?php echo $user_ID; ?>" required>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="degree2">Last Name</label>
                                                    <input type="text" class="form-control mb-4" id="last_name" name="lname" placeholder="User Last Name" value="<?php echo $callUser['lastName'] ?>" required readonly>
                                                </div>
                                            </div>


                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="degree3">Email</label>
                                                            <input type="email" class="form-control mb-4" id="email" name="user_email" placeholder="User Email" value="<?php echo $callUser['userEmail'] ?>" required readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="degree4">Phone Number</label>
                                                            <input type="tel" class="form-control mb-4" id="phone_number" name="phone_number" value="<?php echo $callUser['phone_number'] ?>" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <div class="row">

                                                                <div class="col-md-12">
                                                                    <label for="degree2">Change Your Password</label>
                                                                    <input type="password" class="form-control mb-4" id="c_password" name="c_password" placeholder="Change your Password" required" required>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="loader multi-loader mx-auto" style="display: none;" id="loader"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 text-right mb-5">
                            <button type="submit" class="btn btn-secondary" id="saveBtnModal">Update</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>
</div>


<?php
require_once '../../template/footer.php';
?>

<script src="template/statics/assets/plugins/notification/snackbar/snackbar.min.js"></script>
<script src="template/statics/assets/js/components/notification/custom-snackbar.js"></script>

<script src="auth/js/extra.js"></script>

</body>

</html>