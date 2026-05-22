<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/template/configs/bootstrap.php';

final class AssetImageUploadValidator
{
    private const DEFAULT_MAX_BYTES = 2097152;
    private const DEFAULT_MAX_IMAGES = 10;
    private const MIME_TO_TYPE = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public static function fromPayload($payload, bool $allowEmpty = false): array
    {
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Invalid image format. Expecting base64 image array.');
        }

        if (!$allowEmpty && count($payload) < 1) {
            throw new InvalidArgumentException('At least one image is required.');
        }

        $maxImages = self::maxImages();
        if (count($payload) > $maxImages) {
            throw new InvalidArgumentException('Too many images. Maximum allowed is ' . $maxImages . '.');
        }

        $images = [];
        foreach ($payload as $imageData) {
            $images[] = self::validateDataUri($imageData);
        }

        return $images;
    }

    private static function validateDataUri($imageData): array
    {
        if (!is_string($imageData)) {
            throw new InvalidArgumentException('One or more images are invalid.');
        }

        if (!preg_match('#^data:(image/(?:jpeg|jpg|png|webp));base64,([A-Za-z0-9+/=\r\n]+)$#', trim($imageData), $matches)) {
            throw new InvalidArgumentException('One or more images have an unsupported format.');
        }

        $declaredMime = strtolower($matches[1]);
        if ($declaredMime === 'image/jpg') {
            $declaredMime = 'image/jpeg';
        }

        if (!isset(self::MIME_TO_TYPE[$declaredMime])) {
            throw new InvalidArgumentException('One or more images have an unsupported format.');
        }

        $base64 = preg_replace('/\s+/', '', $matches[2]);
        if (!is_string($base64) || $base64 === '') {
            throw new InvalidArgumentException('One or more images could not be decoded.');
        }

        $estimatedBytes = (int)((strlen($base64) * 3) / 4);
        if ($estimatedBytes > self::maxBytes() + 3) {
            throw new InvalidArgumentException('One or more images exceed ' . self::maxMegabytesLabel() . '.');
        }

        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '') {
            throw new InvalidArgumentException('One or more images could not be decoded.');
        }

        if (strlen($binary) > self::maxBytes()) {
            throw new InvalidArgumentException('One or more images exceed ' . self::maxMegabytesLabel() . '.');
        }

        $imageInfo = @getimagesizefromstring($binary);
        if ($imageInfo === false) {
            throw new InvalidArgumentException('One or more uploaded files are not valid images.');
        }

        $actualMime = strtolower((string)($imageInfo['mime'] ?? ''));
        if ($actualMime !== $declaredMime) {
            throw new InvalidArgumentException('One or more image types do not match their content.');
        }

        return [
            'base64' => base64_encode($binary),
            'mime_type' => self::MIME_TO_TYPE[$actualMime],
        ];
    }

    private static function maxBytes(): int
    {
        $configured = getenv('ASSET_IMAGE_MAX_BYTES');
        if (is_string($configured) && ctype_digit($configured) && (int)$configured > 0) {
            return (int)$configured;
        }

        return self::DEFAULT_MAX_BYTES;
    }

    private static function maxImages(): int
    {
        $configured = getenv('ASSET_IMAGE_MAX_IMAGES');
        if (is_string($configured) && ctype_digit($configured) && (int)$configured > 0) {
            return (int)$configured;
        }

        return self::DEFAULT_MAX_IMAGES;
    }

    private static function maxMegabytesLabel(): string
    {
        $mb = self::maxBytes() / 1048576;
        return rtrim(rtrim(number_format($mb, 2, '.', ''), '0'), '.') . 'MB';
    }
}