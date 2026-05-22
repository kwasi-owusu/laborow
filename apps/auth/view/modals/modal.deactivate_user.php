<?php
require_once('../../controller/GetThisUserController.php');

require_once '../DoUserCors.php';

$page_name         = "edit_this_user";

$editUserToken = DoUserCors::editUserCors($page_name);
$_SESSION['editUserToken'] = $editUserToken;

$user_ID    = $_REQUEST['id'];


$getUser        = GetThisUserController::fetchThisUser($user_ID);
$callUser       = $getUser->fetch(PDO::FETCH_ASSOC);

$user_status    = $callUser['userStatus'];
$thisStatus = '';
switch ($user_status) {
    case 1:
        $thisStatus .= 'Active';
        break;
    case 2:
        $thisStatus .= 'Deactivated';
        break;
    default:
        $thisStatus .= '';
}
?>

<div class="col-xl-12 col-lg-12 col-md-12 layout-spacing">
    <form id="change_user_status_frm" class="section work-experience" action="" method="post" autocomplete="off">
        <div class="info">
            <h5 class="">Change User Status</h5>
            <div class="row">
                <div class="col-md-12 mx-auto">

                    <div class="work-section">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>User Status</label>

                                            <div class="row">

                                                <div class="col-md-12">
                                                    <select class="form-control mb-4" id="wes-from1" id="u_status" name="u_status">

                                                        <optgroup label="Current Role">
                                                            <option value="<?php echo $user_status; ?>"><?php echo $thisStatus; ?></option>
                                                        </optgroup>
                                                        <optgroup label="Change Status">
                                                            <option value="2">Deactivated</option>
                                                            <option value="1">Activate</option>
                                                        </optgroup>
                                                    </select>
                                                    <input type="hidden" class="form-control" id="tkn" name="tkn" 
                                                    value="<?php echo $editUserToken; ?>" required readonly>

                                                    <input type="hidden" class="form-control" id="user_ID" name="user_ID"
                                                    value="<?php echo $user_ID; ?>" required readonly>

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
            <button type="submit" class="btn btn-secondary" id="saveBtn">Submit</button>
        </div>
    </form>
</div>

<script>
    //submit change user role form
    $('#change_user_status_frm').on('submit', function(e) {
        $("#loader").show();
        $("#saveBtn").prop('disabled',true);
        e.preventDefault();
        $.ajax({
            url: "auth/controller/UpdateUserStatusController.php",
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