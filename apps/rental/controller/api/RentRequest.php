<?php

require_once dirname(__DIR__, 2) . '/model/Rentals.php';
require_once dirname(__DIR__, 3) . '/auth/controller/ApiAuthToken.php';

final class RentRequestCTRL
{
    public function requestToRent(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::jsonResponse(['error' => true, 'message' => 'Invalid request method. Use POST.'], 405);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid JSON payload.'], 400);
        }

        $required_fields = [
            'user_ID',
            'request_item',
            'request_description',
            'priority',
            'sample_image',
            'date_needed'
        ];

        foreach ($required_fields as $field) {
            if (empty($data[$field]) || $data[$field] === '_9') {
                self::jsonResponse(['error' => true, 'message' => "Missing or invalid field: $field"], 400);
            }
        }


        $user_ID                = isset($data['user_ID']) ? self::sanitize($data['user_ID']) : null;
        $request_item           = isset($data['request_item']) ? self::sanitize($data['request_item']) : null;
        $request_description    = isset($data['request_description']) ? self::sanitize($data['request_description']) : null;
        $priority               = isset($data['priority']) ? self::sanitize($data['priority']) : null;
        $expected_date          = isset($data['date_needed']) ? self::sanitize($data['date_needed']) : null;

        if (!ctype_digit((string)$user_ID)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid user ID.'], 400);
        }

        self::enforceUserIfTokenPresent((int)$user_ID);

        // Validate and process base64 images
        $allowed_mime_prefixes = ['data:image/jpeg;base64,', 'data:image/png;base64,'];
        $max_size_bytes = 2 * 1024 * 1024;
        $base64_images = $data['sample_image'];
        $image_files = [];

        if (!is_array($base64_images)) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid image format. Expecting base64 image array.'], 400);
        }

        foreach ($base64_images as $imageData) {
            $valid_prefix = null;

            foreach ($allowed_mime_prefixes as $prefix) {
                if (strpos($imageData, $prefix) === 0) {
                    $valid_prefix = $prefix;
                    break;
                }
            }

            if (!$valid_prefix) {
                self::jsonResponse(['error' => true, 'message' => 'One or more images have an unsupported format.'], 400);
            }

            $base64_string = str_replace($valid_prefix, '', $imageData);
            $binary_data = base64_decode($base64_string, true);

            if ($binary_data === false) {
                self::jsonResponse(['error' => true, 'message' => 'One or more images could not be decoded.'], 400);
            }

            if (strlen($binary_data) > $max_size_bytes) {
                self::jsonResponse(['error' => true, 'message' => 'One or more images exceed 2MB.'], 400);
            }

            $mime_type = strpos($valid_prefix, 'jpeg') !== false ? 'jpeg' : 'png';

            $image_files[] = [
                'base64' => base64_encode($binary_data),
                'mime_type' => $mime_type
            ];
        }


        $data = [
            'user_ID' => $user_ID,
            'request_item' => $request_item,
            'request_description' => $request_description,
            'priority' => $priority,
            'expected_date' => $expected_date,
            'imgs' => $image_files
        ];

        $model = new Rentals();
        $updated = $model->request_to_rent($data);

        if ($updated) {
            self::jsonResponse(['error' => false, 'message' => 'Rent request saved successfully.']);
        } else {
            self::jsonResponse(['error' => true, 'message' => 'Failed to save rent request.'], 500);
        }
    }

    private static function enforceUserIfTokenPresent(int $userId): void
    {
        $bearerToken = ApiAuthToken::bearerFromServer($_SERVER);
        if ($bearerToken === null) {
            return;
        }

        $authUser = ApiAuthToken::validate($bearerToken);
        if (!$authUser) {
            self::jsonResponse(['error' => true, 'message' => 'Invalid or expired token.'], 401);
        }

        if ((int)$authUser['user_id'] !== $userId) {
            self::jsonResponse(['error' => true, 'message' => 'Token user does not match requested user.'], 403);
        }
    }

    private static function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    private static function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

(new RentRequestCTRL())->requestToRent();
