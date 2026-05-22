<form id="list_this_asset_frm" action="" method="post" autocomplete="off" enctype="multipart/form-data">

    <?php
    echo isset($_SESSION["isLogin"]) ? "<h5><a href='dashboard.php'>My Account</a></h5>" : "";
    ?>
    <hr />
    <h5>Add Your Listing</h5>
    <p></p>
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

            <input type="hidden" name="add_listing_tkn" value="<?php echo $token; ?>" />

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
            <label>Charge Type <span class="required">*</span></label>
            <select class="form-control" required name="charge_type" id="charge_type" style="border: 1px solid #092;">
                <option value="_9">Select Charge Type</option>
                <option value="per_hr">Per Hour</option>
                <option value="per_day">Per Day</option>
            </select>
        </div>
    </div>


    <div class="row">
        <div class="form-group col-md-12">
            <label>Listing Description <span class="required">*</span></label>
            <textarea class="WYSIWYG" name="asset_description" required cols="40" rows="3" id="asset_description" spellcheck="true"></textarea>
        </div>
    </div>


    <div class="row">
        <div class="form-group col-md-6">
            <label>Date Manufactured/Constructed <span class="required">*</span></label>
            <input required id="manu_date" style="border: 1px solid #092;" class="form-control" name="manu_date" type="date" />
        </div>

        <div class="form-group col-md-6">
            <label>Upload Pictures <span class="required">*</span></label>
            <input required class="form-control" name="listing_img" multiple type="file" />
        </div>
    </div>

    <div class="box-footer">
        <button type="submit" class="btn btn-info pull-right" name="saveBtn" id="saveBtn">Save Listings</button>
    </div>

    <!-- <div class="row">
                                <div class="form-group col-md-12">
                                    <img src="apps/template/statics/assets/imgs/gif_loader.gif" style="height: 90px; width:90px;" id="loader" />
                                </div>
                            </div> -->
</form>