<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

final class MDLAssetStats
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->Connect();
    }

    public function incrementViewCount(int $assetId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE assets
                SET total_view_count = total_view_count + 1
                WHERE assets_ID = :id AND asset_status IN ('Available', '1')
            ");
            $stmt->bindParam(':id', $assetId, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('View Count Update Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function incrementRentCount(int $assetId): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE assets
                SET total_rent_count = total_rent_count + 1
                WHERE assets_ID = :id AND asset_status IN ('Available', '1')
            ");
            $stmt->bindParam(':id', $assetId, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Rent Count Update Failed: ' . $e->getMessage());
            return false;
        }
    }
}
