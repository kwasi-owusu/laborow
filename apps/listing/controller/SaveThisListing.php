<?php

if (!isset($_SESSION)) {
    session_start();
}

class SaveThisListing
{
    public static function saveThisListingCTRL()
    {
        $error      = false;
        $tkn        = strip_tags(trim($_POST['add_listing_tkn']));

        if (isset($_SESSION['add_listing']) && $_SESSION['add_listing'] == $tkn) {

            $listing_category   = strip_tags(trim($_POST['category_ID']));
            $sub_category_ID    = strip_tags(trim($_POST['sub_category_ID']));
            $asset_condition    = strip_tags(trim($_POST['asset_condition']));
            $charge_type        = strip_tags(trim($_POST['charge_type']));
            $asset_title        = strip_tags(trim($_POST['asset_title']));
            $asset_description  = $_POST['asset_desc'];
            $manu_date          = strip_tags(trim($_POST['manu_date']));
            $location_latitude  = strip_tags(trim($_POST['location_latitude']));
            $location_longitude = strip_tags(trim($_POST['location_longitude']));
            $rent_amount        = strip_tags(trim($_POST['rent_amount']));

            $key_details = $asset_title . "-" . $charge_type . "-" . $rent_amount;

            $asset_key = hash_hmac('sha512', $key_details, $listing_category);

            $asset_images       = $_FILES['listing_img'];

            $imgContent             = array();

            //$fileError = $_FILES['listing_img']['error'];

            $code = rand(1, date('Y')) * rand(1, date('Y'));
            $for_slug = $code . "/" . $asset_title;

            function php_slug($string)
            {
                $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower($string));
                return $slug;
            }

            $title_slug = php_slug($for_slug);


            $count = count((array)$_FILES['listing_img']['name']);

            for ($i = 0; $i < $count; $i++) {
                if (is_uploaded_file($_FILES['listing_img']['tmp_name'][$i])) {
                    $mime_type = mime_content_type($_FILES['listing_img']['tmp_name'][$i]);
                    $allowed_file_types = ['image/png', 'image/jpeg'];
                    $file_size = $_FILES["listing_img"]["size"][$i];
                    $file_error = $_FILES["listing_img"]["error"][$i];
                    if (!in_array($mime_type, $allowed_file_types)) {
                        $error = true;
                        echo "Uploaded file not allowed";

                        return;
                    } elseif ($file_size > 2000000) {
                        $error = true;
                        echo "A file exceeds 2MB";
                        return;
                    } elseif ($file_error) {
                        $error = true;
                        echo "There is an upload error";
                        return;
                    }
                }
            }


            if (empty($asset_description)) {
                $error = true;
                echo "Provide a detailed description of your listing ".$asset_description;
                return;
            } elseif ($listing_category == "_9" || $sub_category_ID  == "_9" || $asset_condition  == "_9" || $charge_type  == "_9") {
                $error = true;
                echo "All fields are required";
                return;
            } elseif (empty($rent_amount)) {
                $error = true;
                echo "Please enter your charge amount";
                return;
            } elseif (empty($asset_title)) {
                $error = true;
                echo "Please enter Title";
                return;
            } elseif (!$error) {
                require_once '../model/MDLSaveThisListing.php';

                $tbl    = 'assets';
                $tbl_b  = 'asset_images';
                $data   = array(
                    'lct' => $listing_category,
                    'lst' => $sub_category_ID,
                    'acd' => $asset_condition,
                    'cgt' => $charge_type,
                    'asd' => $asset_description,
                    'md' => $manu_date,
                    'img' => $imgContent,
                    'lat' => $location_latitude,
                    'lng' => $location_longitude,
                    'user' => 1,
                    'img' => $asset_images,
                    'slg' => $title_slug,
                    'title' => $asset_title,
                    'key' => $asset_key,
                    'rent_amount' => $rent_amount
                );

                //$onjSaveList = new MDLSaveThisListing();

                if (MDLSaveThisListing::SaveThisListingMDL($tbl, $tbl_b, $data)) {
                    echo "Successfully saved";
                } else {
                    echo "Error saving. Please try again";
                }
            }
        } else {
            echo "Action Not Permitted";
        }
    }
}

SaveThisListing::saveThisListingCTRL();
