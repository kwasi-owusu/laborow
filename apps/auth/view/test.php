<form id="user_sign_up_form" action="" method="post" autocomplete="off">
                            <div class="info">

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <input type="hidden" class="form-control" required name="tkn" id="tkn" style="border: 1px solid #092;" value="<?php echo $token; ?>" />
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
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Register</button>
                                    </div>
                                </div>

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

                            </div>
                        </form>