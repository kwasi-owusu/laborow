<?php
require_once('../../controller/GetThisUserController.php');

require_once '../DoUserCors.php';

$page_name         = "edit_this_user";

$editUserToken = DoUserCors::editUserCors($page_name);
$_SESSION['editUserToken'] = $editUserToken;

$user_ID    = $_REQUEST['id'];

$thisUserRole   = GetThisUserController::userRoles($user_ID);
$getRoles       = GetThisUserController::fetchAllRoles();
$getUser        = GetThisUserController::fetchThisUser($user_ID);

$getRole        = $thisUserRole->fetch(PDO::FETCH_ASSOC);
$callUser       = $getUser->fetch(PDO::FETCH_ASSOC);
?>

<div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
    <form id="edit_user_frm" class="section work-experience" action="" method="post" autocomplete="off">
        <div class="info">
            <h5 class="">Edit User Profile</h5>
            <div class="row">
                <div class="col-md-11 mx-auto">

                    <div class="work-section">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="degree2">First Name</label>
                                    <input type="text" class="form-control mb-4" id="first_name" name="fname" placeholder="User First Name" value="<?php echo $callUser['firstName'] ?>" required>
                                    <input type="hidden" class="form-control mb-4" id="tkn" readonly name="tkn" value="<?php echo $editUserToken; ?>" required>
                                    <input type="hidden" class="form-control mb-4" id="user_ID" readonly name="user_ID" value="<?php echo $user_ID; ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="degree2">Last Name</label>
                                    <input type="text" class="form-control mb-4" id="last_name" name="lname" placeholder="User Last Name" value="<?php echo $callUser['lastName'] ?>" required>
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
                                            <input type="tel" class="form-control mb-4" id="phone_number" name="phone_number" value="<?php echo $callUser['phone_number'] ?>" placeholder="User Phone" onkeypress="return IsNumeric(event);" ondrop="return false;" onpaste="return false;">
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

                                                        <optgroup label="Current Role">
                                                            <option value="<?php echo $getRole['role_ID']; ?>"><?php echo $getRole['role_desc']; ?></option>
                                                        </optgroup>

                                                        <optgroup label="Change Role">
                                                            <?php
                                                            $cntRoles = $getRoles->rowCount();
                                                            if ($cntRoles > 0) {
                                                                $allRolesLoop = $getRoles->fetchAll();

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
                                                        </optgroup>
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
                </div>
            </div>
        </div>
        <div class="col-md-12 text-right mb-5">
            <button type="submit" class="btn btn-secondary" id="saveBtnModal">Submit</button>
        </div>
    </form>
</div>

<script>
    //submit change user role form
    $('#edit_user_frm').on('submit', function(e) {
        $("#loader").show();
        $("#saveBtnModal").prop('disabled',true);
        e.preventDefault();
        $.ajax({
            url: "auth/controller/UpdateUserController.php",
            method: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            success: function(data) {

                Snackbar.show({
                    text: data,
                    actionTextColor: '#fff',
                    backgroundColor: '#2196f3'
                });
                $("#loader").hide();
                setInterval('location.reload()', 3000);
            }
        });
    });
</script>