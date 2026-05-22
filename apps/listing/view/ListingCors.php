<?php
class ListingCors{
    public static function saveListingCors($page_name)
    {
        $page_is        = $page_name;
        $thi_is_is      = "[Developed by Bahrima InfoSystems with LOVE]";
        $rock_hash      = $page_is . $thi_is_is;

        $listing = hash_hmac('sha512', $rock_hash, $thi_is_is);

        return $listing;
    }
}