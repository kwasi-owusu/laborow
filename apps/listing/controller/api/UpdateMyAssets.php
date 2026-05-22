<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/model/MDLSaveThisListing.php';
require_once dirname(__DIR__, 2) . '/controller/AssetImageUploadValidator.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

final class UpdateMyAssets
{
    public function updateThisListingCTRL(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $dt = json_decode(file_get_contents('php://input'), true);

        if (!is_array($dt)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = [
            'assets_ID', 'category_ID', 'sub_category_ID', 'asset_condition', 'charge_type',
            'asset_title', 'asset_desc', 'location_latitude', 'location_longitude',
            'rent_amount', 'user_ID', 'listing_img'
        ];

        foreach ($required_fields as $field) {
            if (!isset($dt[$field]) || $dt[$field] === '' || $dt[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }

        $user_id = self::sanitize($dt['user_ID']);
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

        // Sanitize inputs
        $data = [
            'assets_ID' => self::sanitize($dt['assets_ID']),
            'lct'       => self::sanitize($dt['category_ID']),
            'lst'       => self::sanitize($dt['sub_category_ID']),
            'acd'       => self::sanitize($dt['asset_condition']),
            'cgt'       => self::sanitize($dt['charge_type']),
            'title'     => self::sanitize($dt['asset_title']),
            'asd'       => self::sanitize($dt['asset_desc']),
            'asp'       => $dt['asset_properties'] ?? null,
            'md'        => isset($dt['manu_date']) ? self::sanitize($dt['manu_date']) : '',
            'lat'       => self::sanitize($dt['location_latitude']),
            'lng'       => self::sanitize($dt['location_longitude']),
            'user'      => $user_id,
            'rent_amount' => self::sanitize($dt['rent_amount']),
            'imgs'      => [],
            'key'       => '',
            'slg'       => ''
        ];

        try {
            $data['imgs'] = AssetImageUploadValidator::fromPayload($dt['listing_img'], true);
        } catch (InvalidArgumentException $e) {
            self::jsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
        }

        // Key and slug
        $data['key'] = hash_hmac('sha512', $data['title'] . '-' . $data['cgt'] . '-' . $data['rent_amount'], $data['lct']);
        $data['slg'] = self::phpSlug(rand(100000, 999999) . '/' . $data['title']);

        // Save
        if (MDLSaveThisListing::UpdateThisListingMDL('assets', 'asset_images', $data)) {
            self::jsonResponse(['error' => false, 'message' => 'Listing updated successfully.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Failed to update listing.'], 500);
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

(new UpdateMyAssets())->updateThisListingCTRL();