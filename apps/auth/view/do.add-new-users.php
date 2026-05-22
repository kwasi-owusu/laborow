<?php
require_once '../../template/index.php';

require_once 'DoUserCors.php';
$page_name         = "add_new_user";
$token             = DoUserCors::loginCors($page_name);

require_once '../controller/GetAllRolesController.php';

$loadRoles = GetAllRolesController::allRoles();

$_SESSION['addUserTkn']  = $token;
?>

<div id="content" class="main-content">
    <div class="layout-px-spacing">

        <div class="account-settings-container layout-top-spacing">

            <div class="account-content">
                <div class="scrollspy-example" data-spy="scroll" data-target="#account-settings-scroll" data-offset="-100">
                    <div class="row">

                        <div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
                            <form id="user_add_frm" class="section work-experience" action="" method="post" autocomplete="off">
                                <div class="info">
                                    <h5 class="">Add a new User</h5>
                                    <div class="row">
                                        <div class="col-md-11 mx-auto">

                                            <div class="work-section">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="degree2">First Name</label>
                                                            <input type="text" class="form-control mb-4" id="first_name" name="fname" placeholder="User First Name" required>
                                                            <input type="hidden" name="addUserTkn" value="<?php echo $token; ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="degree2">Last Name</label>
                                                            <input type="text" class="form-control mb-4" id="last_name" name="lname" placeholder="User Last Name" required>
                                                        </div>
                                                    </div>


                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="degree3">Email <small id="responseHere" style="color:red"></small></label>
                                                                    <input type="email" class="form-control mb-4" id="user_email" name="user_email" placeholder="User Email" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="degree4">Phone Number</label>
                                                                    <input type="tel" class="form-control mb-4" id="phone_number" name="phone_number" placeholder="User Phone" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="degree3">User Password</label>
                                                                    <input type="password" class="form-control mb-4" id="password" name="user_pwd" placeholder="User Password" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="degree4">Confirm Password</label>
                                                                    <input type="password" class="form-control mb-4" id="c_password" name="password_confirmation" placeholder="Confirm Password" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label>User Role</label>

                                                                    <div class="row">

                                                                        <div class="col-md-12">
                                                                            <select class="form-control mb-4" id="wes-from1" id="rle" name="rle">
                                                                                <?php
                                                                                $cntRoles = $loadRoles->rowCount();
                                                                                if ($cntRoles > 0) {
                                                                                    $allRolesLoop = $loadRoles->fetchAll();

                                                                                    foreach ($allRolesLoop as $rls) {
                                                                                ?>
                                                                                        <option value="<?php echo $rls['role_ID']; ?>"><?php echo $rls['role_desc']; ?></option>
                                                                                    <?php
                                                                                    }
                                                                                } else {
                                                                                    ?>
                                                                                    <option value="000">No Role Available</option>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                            </select>
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
                                            <p id="responseHere"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-right mb-5">
                                    <button type="submit" class="btn btn-secondary" id="saveBtn">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
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