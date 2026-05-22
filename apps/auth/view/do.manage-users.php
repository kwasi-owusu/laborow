<?php
require_once '../../template/index.php';

require_once 'DoUserCors.php';
$page_name         = "add_new_user";
$token             = DoUserCors::loginCors($page_name);

require_once '../controller/GetAllUsers.php';

$loadRoles = GetAllUsers::doAllUsers();

?>

<div id="content" class="main-content">
    <div class="layout-px-spacing">

        <div class="row layout-top-spacing">

            <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                <div class="widget-content widget-content-area br-6">
                <div class="w-content">
                        <span class="w-value" style="padding: 15px; font-size:20px; font-weight:bolder;">Manage Users</span>
                    </div>
                    <table id="html5-extension" class="table table-hover non-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>User Role</th>
                                <th>Merchant</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th class="dt-no-sorting">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $fetchUsers = $loadRoles->fetchAll();
                            foreach ($fetchUsers as $us) {
                                $myStatus = '';
                                $userStatus = $us['userStatus'];
                                switch ($userStatus){
                                    case 1:
                                        $myStatus .='Activated';
                                        break;
                                    case 2:
                                        $myStatus .='Deactivated';
                                        break;

                                    default:
                                        $myStatus .='Status Not Available';
                                }
                            ?>
                                <tr>
                                    <td><?php echo $us['firstName']; ?></td>
                                    <td><?php echo $us['lastName']; ?></td>
                                    <td><?php echo $us['userEmail']; ?></td>
                                    <td><?php echo $us['phone_number']; ?></td>
                                    <td><?php echo $us['role_desc']; ?></td>
                                    <td><?php echo isset($us['name']) ? $us['name']: ''; ?></td>
                                    <td><?php echo isset($us['branch_name']) ? $us['branch_name'] : ''; ?></td>
                                    <td><?php echo $myStatus; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <!-- <button type="button" class="btn btn-dark btn-sm">Open</button> -->
                                            <button type="button" class="btn btn-dark btn-sm dropdown-toggle dropdown-toggle-split" id="dropdownMenuReference28" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-reference="parent">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down">
                                                    <polyline points="6 9 12 15 18 9"></polyline>
                                                </svg>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuReference28">

                                                <a class="dropdown-item" href="javascript:void(o);" data-id="<?php echo $us['user_ID']; ?>" 
                                                onclick="editThisUser(this)" data-toggle="modal" data-target="#manageUserModalLG">
                                                    Edit User
                                                </a>
                                                
                                                <a class="dropdown-item" href="javascript:void(o);" data-id="<?php echo $us['user_ID']; ?>" 
                                                onclick="changePassword(this)" data-toggle="modal" data-target="#manageUserModalSM">
                                                    Change Password
                                                </a>

                                                <a class="dropdown-item" href="javascript:void(o);"  data-id="<?php echo $us['user_ID']; ?>" 
                                                onclick="deactivateUser(this)" data-toggle="modal" data-target="#manageUserModalSM">
                                                Change Status
                                                </a>                                            
                                            
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
</div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" id="manageUserModalLG" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Manage User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="userModalContentLG">
                
            </div>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" id="manageUserModalSM" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Manage User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="userModalContentSM">
                
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