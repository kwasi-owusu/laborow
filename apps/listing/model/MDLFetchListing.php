<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

final class MDLFetchListing
{
    private const LEGACY_CATEGORY_NAMES = [
        1 => ['Properties'],
        2 => ['Sports', 'Sport items', 'Sports Items'],
        3 => ['Events', 'Event Mgt', 'Events Mgt'],
        4 => ['Electronics'],
        5 => ['Fishing Tools', 'Fishing tools'],
        6 => ['Farm Tools'],
        7 => ['Automobile'],
        8 => ['HomeTools', 'Home Tools'],
        9 => ['Advertisement', 'Advertisement Space', 'Advertising Space'],
    ];

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::connect();
        if (!$this->pdo) {
            error_log("Database connection failed in MDLFetchListing");
        }
    }

    private function baseSelectQuery(): string
    {
        return "
            SELECT 
                assets.*, 
                grouped_images.asset_images, 
                asset_category.category_desc, 
                asset_sub_category.asset_sub_category_desc,
                CASE 
                    WHEN assets.rented_by = 'individual' THEN users.first_name
                    WHEN assets.rented_by = 'rent_business' THEN businesses.business_name
                    ELSE NULL
                END AS rented_by_name
            FROM assets
            LEFT JOIN (
                SELECT 
                    asset_ID, 
                    JSON_ARRAYAGG(asset_image_save) AS asset_images
                FROM asset_images
                GROUP BY asset_ID
            ) AS grouped_images ON assets.assets_ID = grouped_images.asset_ID
            LEFT JOIN asset_category ON assets.category_ID = asset_category.asset_category_ID
            LEFT JOIN asset_sub_category ON assets.sub_category_ID = asset_sub_category.asset_sub_category_ID
            LEFT JOIN users ON assets.user_ID = users.user_id AND assets.rented_by = 'individual'
            LEFT JOIN businesses ON assets.user_ID = businesses.user_id AND assets.rented_by = 'rent_business'
        ";
    }

    private function availableAssetClause(): string
    {
        return "assets.asset_status IN ('Available', '1')";
    }

    private function decodeAssetProperties(array $rows): array
    {
        foreach ($rows as &$row) {
            if (isset($row['asset_properties']) && is_string($row['asset_properties'])) {
                $decoded = json_decode($row['asset_properties'], true);
                $row['asset_properties'] = $decoded ?? null;
            }

            if (isset($row['asset_images']) && is_string($row['asset_images'])) {
                $decodedImages = json_decode($row['asset_images'], true);
                $row['asset_images'] = $decodedImages ?? [];
            }
        }
        return $rows;
    }

    private function normalizeTextFilter($value): string
    {
        return strtolower(trim((string)$value));
    }

    private function slugifyTextFilter($value): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '-', $this->normalizeTextFilter($value));
        return trim((string)$slug, '-');
    }

    private function addCategoryFilter(array &$whereClauses, array &$params, $category): void
    {
        $category = trim((string)$category);
        if ($category === '') {
            return;
        }

        if (ctype_digit($category)) {
            $legacyNames = self::LEGACY_CATEGORY_NAMES[(int)$category] ?? [];
            $legacyClauses = [];

            foreach ($legacyNames as $index => $legacyName) {
                $param = ':legacy_category_' . $index;
                $legacyClauses[] = "LOWER(TRIM(asset_category.category_desc)) = $param";
                $params[$param] = $this->normalizeTextFilter($legacyName);
            }

            $categoryClause = "assets.category_ID = :category_ID";
            if (!empty($legacyClauses)) {
                $categoryClause = '(' . $categoryClause . ' OR ' . implode(' OR ', $legacyClauses) . ')';
            }

            $whereClauses[] = $categoryClause;
            $params[':category_ID'] = (int)$category;
            return;
        }

        $whereClauses[] = "(
            LOWER(TRIM(asset_category.category_desc)) = :category_text
            OR LOWER(REPLACE(TRIM(asset_category.category_desc), ' ', '-')) = :category_slug
        )";
        $params[':category_text'] = $this->normalizeTextFilter($category);
        $params[':category_slug'] = $this->slugifyTextFilter($category);
    }

    private function addSubCategoryFilter(array &$whereClauses, array &$params, $subCategory): void
    {
        $subCategory = trim((string)$subCategory);
        if ($subCategory === '') {
            return;
        }

        if (ctype_digit($subCategory)) {
            $whereClauses[] = "assets.sub_category_ID = :sub_category_ID";
            $params[':sub_category_ID'] = (int)$subCategory;
            return;
        }

        $whereClauses[] = "(
            LOWER(TRIM(asset_sub_category.asset_sub_category_desc)) = :sub_category_text
            OR LOWER(REPLACE(TRIM(asset_sub_category.asset_sub_category_desc), ' ', '-')) = :sub_category_slug
            OR LOWER(TRIM(asset_sub_category.sub_category_slug)) = :sub_category_slug
        )";
        $params[':sub_category_text'] = $this->normalizeTextFilter($subCategory);
        $params[':sub_category_slug'] = $this->slugifyTextFilter($subCategory);
    }

    public function fetchFilteredAssets(array $filters, int $limit = 10, int $offset = 0): array
    {
        $whereClauses = [$this->availableAssetClause()];
        $params = [];

        if (!empty($filters['keyword'])) {
            $whereClauses[] = "(assets.asset_title LIKE :kw OR assets.asset_description LIKE :kw)";
            $params[':kw'] = '%' . $filters['keyword'] . '%';
        }

        $this->addCategoryFilter($whereClauses, $params, $filters['category_ID'] ?? null);
        $this->addSubCategoryFilter($whereClauses, $params, $filters['sub_category_ID'] ?? null);

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $whereClauses[] = "assets.rent_amount >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $whereClauses[] = "assets.rent_amount <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['rented_by'])) {
            $whereClauses[] = "assets.rented_by = :rented_by";
            $params[':rented_by'] = $filters['rented_by'];
        }

        if (!empty($filters['lat']) && !empty($filters['lng']) && !empty($filters['radius'])) {
            $whereClauses[] = "
                (
                    6371 * ACOS(
                        LEAST(1,
                            COS(RADIANS(:lat)) *
                            COS(RADIANS(CAST(TRIM(assets.asset_location_latitude) AS DECIMAL(10,6)))) *
                            COS(RADIANS(assets.assets_location_longitude) - RADIANS(:lng)) +
                            SIN(RADIANS(:lat)) *
                            SIN(RADIANS(CAST(TRIM(assets.asset_location_latitude) AS DECIMAL(10,6))))
                        )
                    )
                ) <= :radius
            ";

            $params[':lat'] = $filters['lat'];
            $params[':lng'] = $filters['lng'];
            $params[':radius'] = $filters['radius'];
        }

        $whereClause = implode(' AND ', $whereClauses);
        $query = $this->baseSelectQuery() . " WHERE $whereClause ORDER BY assets.system_date DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function fetchListingMDL(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause());
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function fetchSingleListingBySlugMDL(string $slug): ?array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " AND assets.asset_slug = :slg");
        $stmt->bindParam(':slg', $slug, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decodeAssetProperties([$row])[0] : null;
    }

    public function fetchListingByCategoryMDL($categoryID): array
    {
        $whereClauses = [$this->availableAssetClause()];
        $params = [];
        $this->addCategoryFilter($whereClauses, $params, $categoryID);

        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . implode(' AND ', $whereClauses));
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function fetchListingImagesMDL(int $asset_ID): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM asset_images WHERE asset_ID = :asd");
        $stmt->bindParam(':asd', $asset_ID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchListingBySubCategoryMDL(string $subCategorySlug): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " AND asset_sub_category.sub_category_slug = :sct");
        $stmt->bindParam(':sct', $subCategorySlug, PDO::PARAM_STR);
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_featured_assets_random_ten(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE assets.is_featured = 1 AND " . $this->availableAssetClause() . " ORDER BY RAND() LIMIT 10");
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_popular_assets(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " ORDER BY assets.total_rent_count DESC LIMIT 10");
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_most_viewed(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " ORDER BY assets.total_view_count DESC LIMIT 10");
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_single_asset(int $assetID): ?array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE assets.assets_ID = :id AND " . $this->availableAssetClause() . " LIMIT 1");
        $stmt->bindParam(':id', $assetID, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->decodeAssetProperties([$row])[0] : null;
    }

    public function select_asset_by_category($category_id): array
    {
        $whereClauses = [$this->availableAssetClause()];
        $params = [];
        $this->addCategoryFilter($whereClauses, $params, $category_id);

        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . implode(' AND ', $whereClauses));
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_asset_by_sub_category($subCategoryID): array
    {
        $whereClauses = [$this->availableAssetClause()];
        $params = [];
        $this->addSubCategoryFilter($whereClauses, $params, $subCategoryID);

        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . implode(' AND ', $whereClauses));
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_all_assets(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " ORDER BY assets.system_date DESC");
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_random_ten_assets(): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " ORDER BY RAND() LIMIT 10");
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function select_my_assets($user_ID): array
    {
        $stmt = $this->pdo->prepare($this->baseSelectQuery() . " WHERE " . $this->availableAssetClause() . " AND assets.user_ID = :u");
        $stmt->bindParam(':u', $user_ID, PDO::PARAM_INT);
        $stmt->execute();
        return $this->decodeAssetProperties($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
