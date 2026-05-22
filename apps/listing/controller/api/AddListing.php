<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/model/MDLSaveThisListing.php';
require_once dirname(__DIR__, 2) . '/controller/AssetImageUploadValidator.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

final class SaveThisListing
{
    public function saveThisListingCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $dt = json_decode(file_get_contents('php://input'), true);

        if (!is_array($dt)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = [
            'user_ID', 'category_ID', 'sub_category_ID', 'asset_condition', 'charge_type',
            'asset_title', 'asset_desc', 'asset_properties', 'location_latitude', 'location_longitude', 'rent_amount', 'listing_img'
        ];

        foreach ($required_fields as $field) {
            if (empty($dt[$field]) || $dt[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }

        // Sanitize input
        $listing_category   = self::sanitize($dt['category_ID']);
        $sub_category_ID    = self::sanitize($dt['sub_category_ID']);
        $asset_condition    = self::sanitize($dt['asset_condition']);
        $charge_type        = self::sanitize($dt['charge_type']);
        $asset_title        = self::sanitize($dt['asset_title']);
        $asset_description  = self::sanitize($dt['asset_desc']);
        $asset_properties   = $dt['asset_properties'];
        $manu_date          = isset($dt['manu_date']) ? self::sanitize($dt['manu_date']) : '';
        $location_latitude  = self::sanitize($dt['location_latitude']);
        $location_longitude = self::sanitize($dt['location_longitude']);
        $rent_amount        = self::sanitize($dt['rent_amount']);
        $user_id            = self::sanitize($dt['user_ID']);

        if (!ctype_digit((string)$user_id)) {
            self::jsonResponse(['error' => true, 'message' => 'User ID must be numeric.'], 400);
        }

        // Compatibility bridge: old mobile clients do not send a bearer token yet.
        // If a token is present, enforce it; if absent, keep legacy behavior temporarily.
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken !== null) {
            $authUser = ApiAuthToken::validate($bearerToken);
            if (!$authUser) {
                self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
            }

            if ((int)$authUser['user_id'] !== (int)$user_id) {
                self::jsonResponse(['error' => true, 'message' => 'Token user does not match listing user.'], 403);
            }
        }

        try {
            $image_files = AssetImageUploadValidator::fromPayload($dt['listing_img']);
        } catch (InvalidArgumentException $e) {
            self::jsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
        }

        // Generate asset key and slug
        $key_details = $asset_title . '-' . $charge_type . '-' . $rent_amount;
        $asset_key = hash_hmac('sha512', $key_details, $listing_category);
        $title_slug = self::phpSlug(rand(100000, 999999) . '/' . $asset_title);

        $data = [
            'lct' => $listing_category,
            'lst' => $sub_category_ID,
            'acd' => $asset_condition,
            'cgt' => $charge_type,
            'asd' => $asset_description,
            'asp' => $asset_properties,
            'md'  => $manu_date,
            'lat' => $location_latitude,
            'lng' => $location_longitude,
            'user' => $user_id,
            'imgs' => $image_files,
            'slg' => $title_slug,
            'title' => $asset_title,
            'key' => $asset_key,
            'rent_amount' => $rent_amount
        ];

        if (MDLSaveThisListing::SaveThisListingMDL('assets', 'asset_images', $data)) {
            self::jsonResponse(['error' => false, 'message' => 'Listing saved successfully.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Failed to save listing. Please try again.'], 500);
        }
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function phpSlug(string $string): string
    {
        return trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($string)), '-');
    }

    private static function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new SaveThisListing())->saveThisListingCTRL();