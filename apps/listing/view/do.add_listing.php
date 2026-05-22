<?php
$page_title = "List Assets";

require_once dirname(__DIR__, 2) . '/template/head.php';

if (!isset($_SESSION["isLogin"])) {
    echo '<script>
			window.location = "login";
		</script>';
}

require_once dirname(__DIR__) . '/controller/ListingEnum.php';
require_once dirname(__DIR__) . '/controller/CTRLSecureListing.php';

$page_name     = ListingEnum::listing_page_name->value;
$hash_key      = ListingEnum::secure_form_cors_hash->value;


$create_token       = new CTRLSecureListing();
$add_listing_token  = $create_token->is_form_hash_valid($page_name, $hash_key);
$_SESSION['add_listing'] = $add_listing_token;


$user_email = $_SESSION['email'];

$account_security = new CTRLSecureListing();

$check_if_my_email_is_verified = $account_security->is_email_verified($user_email);

$is_email_verified = $check_if_my_email_is_verified['is_email_verified'];

require_once dirname(__DIR__, 2) . '/settings/controller/MainCTRLGEtListingCategories.php';

$google_maps_browser_key = trim((string)(getenv('GOOGLE_MAPS_BROWSER_KEY') ?: ''));
?>

<body>
    <?php
    require_once dirname(__DIR__, 2) . '/template/other_header.php';
    //require_once "../../template/other_header.php";
    ?>

    <!--End header-->
    <main class="main">
        <section class="banners mb-25">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-sm-12 offset-md-2">
                        <span style="color: #e31d1d;">
                            <?php
                            echo $is_email_verified = "No" ? "Your Email is Not Verified. Please check your email for the link to verify your email" : "";
                            ?>
                        </span>

                        <hr />
                        <h5>Add Your Listing</h5>
                        <p></p>
                        <form id="list_asset_frm" action="" method="post" autocomplete="off" enctype="multipart/form-data">
                            <div class="info">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label>Listing Title <small>Example: Brand New Sony Digital Camera.</small><span class="required">*</span></label>
                                        <input type="text" class="" required name="asset_title" id="asset_title" style="border: 1px solid #092;" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Listing Category <span class="required">*</span></label>
                                        <select class="form-control" required name="category_ID" id="asset_category_b" style="border: 1px solid #092;">
                                            <option value="_9">Select a Category</option>
                                            <optgroup label="All Categories">
                                                <?php
                                                $allListingCategories = MainCTRLGEtListingCategories::GetListingCategoriesCTR();

                                                foreach ($allListingCategories as $sct) {
                                                ?>
                                                    <option value="<?php echo $sct['asset_category_ID']; ?>"><?php echo $sct['category_desc']; ?></option>
                                                <?php
                                                }
                                                ?>
                                            </optgroup>

                                        </select>

                                        <input type="hidden" name="add_listing_tkn" value="<?php echo $add_listing_token; ?>" />

                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Listing Sub-Category <span class="required">*</span></label>
                                        <select class="form-control" required name="sub_category_ID" id="asset_sub_category" style="border: 1px solid #092;">
                                            <option value="_9">Select a Subcategory</option>
                                            <optgroup label="Choose Sub Category" id="sub_cat_here_b"></optgroup>
                                        </select>

                                    </div>
                                </div>


                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Asset Condition <span class="required">*</span></label>
                                        <select class="form-control" required name="asset_condition" id="asset_condition" style="border: 1px solid #092;">
                                            <option value="_9">Select a Condition</option>
                                            <option value="New">New</option>
                                            <option value="Fairly Used">Fairly Used</option>
                                            <option value="Used">Used</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Date Manufactured/Constructed <span class="required">*</span></label>
                                        <input type="date" class="form-control" required name="manu_date" id="manu_date" style="border: 1px solid #092;" />
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Charge Type <span class="required">*</span></label>
                                        <select class="form-control" required name="charge_type" id="charge_type" style="border: 1px solid #092;">
                                            <option value="_9">Select Charge Type</option>
                                            <option value="per_hr">Per Hour</option>
                                            <option value="per_day">Per Day</option>
                                        </select>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Charge Amount <span class="required">*</span></label>
                                        <input type="text" class="form-control" required name="rent_amount" id="rent_amount" style="border: 1px solid #092;" onkeypress="return IsNumeric(event);" ondrop="return false;" />
                                    </div>

                                </div>

                                <div class="input-field">
                                    <label>Upload Multiple Pictures <span class="required">*</span></label>
                                    <div class="input-images-1" id="asset_images_uploader" style="padding-top: .5rem;"></div>
                                </div>

                                <hr />


                                <h4>Asset Location</h4>

                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label>Location Latitude <span class="required">*</span></label>
                                        <input required id="lat" style="border: 1px solid #092;" class="form-control" name="location_latitude" type="text" />
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Location Longitude <span class="required">*</span></label>
                                        <input required id="lng" style="border: 1px solid #092;" class="form-control" name="location_longitude" type="text" />

                                    </div>
                                </div>


                                <!--
                                <div class="row">
                                    <label>Providing the exact location helps in better search results for your item</label>
                                    <div class="form-group col-md-12">
                                        <div id="this_map" style="width: 100%; height:450px;"></div>
                                    </div>
                                </div>

                                            -->

                                <div class="row">
                                    <label>Asset Details</label>
                                    <div class="form-group col-12">
                                        <textarea style="border: 1px solid #092;" class="WYSIWYG" name="asset_desc" cols="40" rows="3" id="prop_desc" spellcheck="true"></textarea>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Save Listing
                                    </button>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="loader multi-loader mx-auto" style="display: none;" id="list_loader"></div>
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


    <!-- Vendor JS-->
    <?php
    require_once dirname(__DIR__, 2) . '/template/footer.php';
    ?>
    <!--====== back-to-top ======-->
    <a href="#" class="back-to-top"><i class="ti-angle-up"></i></a>
    <!--====== Jquery js ======-->


    <?php
    require_once dirname(__DIR__, 2) . '/template/footer_script.php';
    ?>


    <script src="apps/template/statics/assets/upload_preview/js/image-uploader.min.js"></script>
    <script>
        $('#asset_images_uploader').imageUploader({
            imagesInputName: 'listing_img'
        });
    </script>


    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                x.innerHTML = "Geolocation is not supported by this browser.";
            }
        }

        function showPosition(position) {
            $('#lat').val(position.coords.latitude);
            $('#lng').val(position.coords.longitude);
        }

        getLocation();
    </script>


    <?php if ($google_maps_browser_key !== ''): ?>
        <script src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($google_maps_browser_key, ENT_QUOTES, 'UTF-8') ?>&callback=initMap&v=weekly" defer></script>
    <?php endif; ?>


    <!--
    <script>
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var currentLatitude = position.coords.latitude;
                var currentLongitude = position.coords.longitude;

                var infoWindowHTML = "Latitude: " + currentLatitude + "<br>Longitude: " + currentLongitude;
                var infoWindow = new google.maps.InfoWindow({
                    map: map,
                    content: infoWindowHTML
                });
                var currentLocation = {
                    lat: currentLatitude,
                    lng: currentLongitude
                };
                infoWindow.setPosition(currentLocation);
                $("#lat").val(currentLatitude);
                $("#lng").val(currentLongitude);
            });
        }


        var map; //Will contain map object.
        var marker = false; ////Has the user plotted their location marker? 

        //Function called to initialize / create the map.
        //This is called when the page has loaded.
        function initMap() {

            //The center location of our map.
            var centerOfMap = new google.maps.LatLng(5.603717, -0.186964);

            //Map options.
            var options = {
                center: centerOfMap, //Set center.
                zoom: 18 //The zoom value.
            };


            //Create the map object.
            map = new google.maps.Map(document.getElementById('this_map'), options);

            //Listen for any clicks on the map.
            google.maps.event.addListener(map, 'click', function(event) {
                //Get the location that the user clicked.
                var clickedLocation = event.latLng;
                //If the marker hasn't been added.
                if (marker === false) {
                    //Create the marker.
                    marker = new google.maps.Marker({
                        position: clickedLocation,
                        map: map,
                        draggable: true //make it draggable
                    });
                    //Listen for drag events!
                    google.maps.event.addListener(marker, 'dragend', function(event) {
                        markerLocation();
                    });
                } else {
                    //Marker has already been added, so just change its location.
                    marker.setPosition(clickedLocation);
                }
                //Get the marker's location.
                markerLocation();
            });
        }

        //This function will get the marker's current location and then add the lat/long
        //values to our textfields so that we can save the location.
        function markerLocation() {
            //Get location.
            var currentLocation = marker.getPosition();
            //Add lat and lng values to a field that we can save.
            document.getElementById('lat').value = currentLocation.lat(); //latitude
            document.getElementById('lng').value = currentLocation.lng(); //longitude
        }

        //Load the map when the page has finished loading.
        google.maps.event.addDomListener(window, 'load', initMap);
    </script> 
    -->

    <script src="apps/listing/js/extra.js"></script>

</body>

</html>

