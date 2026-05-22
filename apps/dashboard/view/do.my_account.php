<?php
$page_title = "My Account Dashboard";

require_once dirname(__DIR__, 2) . '/template/head.php';

if (!isset($_SESSION["isLogin"])) {
    echo '<script>
			window.location = "login";
		</script>';
}

require_once dirname(__DIR__, 2) . '/settings/controller/MainCTRLGEtListingCategories.php';

require_once dirname(__DIR__, 2) . '/auth/controller/CTRLSecureLogin.php';

$user_email = $_SESSION['email'];

$account_security = new CTRLSecureLogin();

$check_if_my_email_is_verified = $account_security->is_email_verified($user_email);

$is_email_verified = $check_if_my_email_is_verified['is_email_verified'];

?>

<body>
    <?php
    require_once dirname(__DIR__, 2) . '/template/other_header.php';

    ?>

    <!--End header-->
    <main class="main">
        <section class="banners mb-25">
            <div class="container">
                <div class="page-content pt-150 pb-150">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-10 m-auto">
                                <div class="row">
                                    <div class="card-header">
                                        <h3 class="mb-0">Hello <?php echo $_SESSION['first_name']; ?>!</h3>
                                        <p style="color: #e31d1d;">
                                            <?php
                                            echo $is_email_verified = "No" ? "Your Email is Not Verified. Please check your email for the link to verify your email" : "";
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="dashboard-menu">
                                            <ul class="nav flex-column" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" href="#my_assets" role="tab" aria-controls="dashboard" aria-selected="false"><i class="fi-rs-settings-sliders mr-10"></i>My Listed Assets</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="orders-tab" data-bs-toggle="tab" href="#rent_history" role="tab" aria-controls="orders" aria-selected="false"><i class="fi-rs-shopping-bag mr-10"></i>Assets I have rented</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="track-orders-tab" data-bs-toggle="tab" href="#track-orders" role="tab" aria-controls="track-orders" aria-selected="false"><i class="fi-rs-shopping-cart-check mr-10"></i>Track Your Order</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="account-detail-tab" data-bs-toggle="tab" href="#account-detail" role="tab" aria-controls="account-detail" aria-selected="true"><i class="fi-rs-user mr-10"></i>Account details</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="logout"><i class="fi-rs-sign-out mr-10"></i>Logout</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="tab-content account dashboard-content pl-50">
                                            <div class="tab-pane fade active show" id="my_assets" role="tabpanel" aria-labelledby="dashboard-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="mb-0">Hello <?php echo $_SESSION['first_name']; ?>!</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="buttons-datatables" class="display table buttons-datatables" style="width:100%; font-size:small;">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col" style="width: 10px;">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input fs-15" type="checkbox" id="checkAll" value="option">
                                                                        </div>
                                                                    </th>
                                                                    <th>Asset</th>
                                                                    <th>Condition</th>
                                                                    <th>Charge Amount</th>
                                                                    <th>Charge Type</th>
                                                                    <th>Asset Status</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <th scope="row">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input fs-15" type="checkbox" name="checkAll" value="option1">
                                                                        </div>
                                                                    </th>
                                                                    <td>Wheelbarrow</td>
                                                                    <td>Slightly Used</td>
                                                                    <td>8</td>
                                                                    <td>Per day</td>
                                                                    <td>Available</td>
                                                                    <td>
                                                                        <div class="dropdown d-inline-block">
                                                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                <i class="ri-more-fill align-middle"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                                <li><a data-bs-toggle="modal" data-bs-target="#myModal" href="#!" class="dropdown-item edit-item-btn"><i class="ri-home-fill align-bottom me-2 text-muted"></i>
                                                                                        More Details</a>
                                                                                </li>
                                                                                <li><a href="#!" class="dropdown-item"><i class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                                                        Edit Details</a>
                                                                                </li>
                                                                                <li><a href="#!" class="dropdown-item edit-item-btn"><i class="ri-user-fill align-bottom me-2 text-muted"></i>
                                                                                        Edit Status</a>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="rent_history" role="tabpanel" aria-labelledby="orders-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="mb-0">Your Orders</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="table-responsive">
                                                            <table id="buttons-datatables" class="display table buttons-datatables" style="width:100%; font-size:small;">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col" style="width: 10px;">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input fs-15" type="checkbox" id="checkAll" value="option">
                                                                            </div>
                                                                        </th>
                                                                        <th>Asset</th>
                                                                        <th>Condition</th>
                                                                        <th>Charge Amount</th>
                                                                        <th>Charge Type</th>
                                                                        <th>Today Period</th>
                                                                        <th>Total Charge(GHS)</th>
                                                                        <th>Date Rented</th>
                                                                        <th>Date To Release</th>
                                                                        <th>Actions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input fs-15" type="checkbox" name="checkAll" value="option1">
                                                                            </div>
                                                                        </th>
                                                                        <td>Wheelbarrow</td>
                                                                        <td>Slightly Used</td>
                                                                        <td>8</td>
                                                                        <td>Per day</td>
                                                                        <td>3</td>
                                                                        <td>24</td>
                                                                        <td>2022-10-01</td>
                                                                        <td>2022-10-03</td>
                                                                        <td>
                                                                            <div class="dropdown d-inline-block">
                                                                                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                                    <i class="ri-more-fill align-middle"></i>
                                                                                </button>
                                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                                    <li><a data-bs-toggle="modal" data-bs-target="#myModal" href="#!" class="dropdown-item edit-item-btn"><i class="ri-home-fill align-bottom me-2 text-muted"></i>
                                                                                            More Details</a></li>
                                                                                    <li><a href="#!" class="dropdown-item"><i class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                                                            Early Released</a></li>
                                                                                    <li><a href="#!" class="dropdown-item edit-item-btn"><i class="ri-user-fill align-bottom me-2 text-muted"></i>
                                                                                            Request Extension</a></li>
                                                                                    <li>
                                                                                        <a data-bs-toggle="modal" data-bs-target="#userdetails" href="#!" class="dropdown-item remove-item-btn">
                                                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                                                            Message Owner
                                                                                        </a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="track-orders" role="tabpanel" aria-labelledby="track-orders-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h3 class="mb-0">Orders tracking</h3>
                                                    </div>
                                                    <div class="card-body contact-from-area">
                                                        <p>To track your order please enter your OrderID in the box below and press "Track" button. This was given to you on your receipt and in the confirmation email you should have received.</p>
                                                        <div class="row">
                                                            <div class="col-lg-8">
                                                                <form class="contact-form-style mt-30 mb-50" action="#" method="post">
                                                                    <div class="input-style mb-20">
                                                                        <label>Order ID</label>
                                                                        <input name="order-id" placeholder="Found in your order confirmation email" type="text" />
                                                                    </div>
                                                                    <div class="input-style mb-20">
                                                                        <label>Billing email</label>
                                                                        <input name="billing-email" placeholder="Email you used during checkout" type="email" />
                                                                    </div>
                                                                    <button class="submit submit-auto-width" type="submit">Track</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="account-detail" role="tabpanel" aria-labelledby="account-detail-tab">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Account Details</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <form method="post" name="enq">
                                                            <div class="row">
                                                                <div class="form-group col-md-6">
                                                                    <label>First Name <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="name" type="text" />
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    <label>Last Name <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="phone" />
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label>Display Name <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="dname" type="text" />
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label>Email Address <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="email" type="email" />
                                                                </div>
                                                                
                                                                <div class="form-group col-md-12">
                                                                    <label>New Password <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="npassword" type="password" />
                                                                </div>
                                                                <div class="form-group col-md-12">
                                                                    <label>Confirm Password <span class="required">*</span></label>
                                                                    <input required="" class="form-control" name="cpassword" type="password" />
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <button type="submit" class="btn btn-fill-out submit font-weight-bold" name="submit" value="Submit">Save Change</button>
                                                                </div>
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
                    </div>
                </div>

            </div>
        </section>
    </main>


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