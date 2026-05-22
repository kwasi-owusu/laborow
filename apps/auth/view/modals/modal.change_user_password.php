<?php
require_once('../../controller/GetThisUserController.php');

require_once '../DoUserCors.php';

$page_name         = "edit_this_user";

$editUserToken = DoUserCors::editUserCors($page_name);
$_SESSION['userPwdTkn'] = $editUserToken;

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
            <h5 class="">Change Password</h5>
            <div class="row">
                <div class="col-md-12 mx-auto">

                    <div class="work-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="degree2">New Password</label>
                                    <input type="password" class="form-control mb-4" id="password" name="user_pwd" placeholder="Enter New Password" required>
                                    <input type="hidden" class="form-control mb-4" id="tkn" readonly name="tkn" value="<?php echo $editUserToken; ?>" required>
                                    <input type="hidden" class="form-control mb-4" id="user_ID" readonly name="user_ID" value="<?php echo $user_ID; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="degree2">Confirm Password</label>
                                    <input type="password" class="form-control mb-4" id="c_password" name="c_password" placeholder="Confirm Password" required" required>
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
    <span id="responseHere"></span>
</div>

<script>
    //submit change user role form
    $('#edit_user_frm').on('submit', function(e) {
        $("#loader").show();
        $("#saveBtnModal").prop('disabled',true);
        e.preventDefault();
        $.ajax({
            url: "auth/controller/UpdateUserPwdController.php",
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

<script>
    $('#c_password').on('keyup', function () {
    let firsPassword    = $('#password').val();
    let secondPassword  = $('#c_password').val();

    if (firsPassword !== secondPassword){
        $('#responseHere').text("Passwords do not Match");
        $('#saveBtn').prop('disabled', true);
    }
    else {
        $('#responseHere').text(" ");
        $('#saveBtn').prop('disabled', false);
    }
});
</script>