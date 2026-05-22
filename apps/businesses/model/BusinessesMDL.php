<?php
require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

final class BusinessesMDL
{
    public function AllRentalBusinesses(array $data): array|null
    {
        try {
            $pdo = Connection::connect();
            $stmt = $pdo->prepare("
                SELECT business_id, user_id, business_type, business_name, business_phone_number, website, 
                       business_email, business_profile, business_address, business_state, business_city, business_status
                FROM businesses 
                LIMIT :offset, :limit
            ");

            $stmt->bindValue(':offset', $data['offset'], PDO::PARAM_INT);
            $stmt->bindValue(':limit', $data['limit'], PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("List All Rental Businesses Error: " . $e->getMessage());
            return null;
        }
    }


    public function getSingleRentalBusiness(int $businessId): array|null
    {
        try {
            $pdo = Connection::connect();

            $stmt = $pdo->prepare("
            SELECT business_id, user_id, business_type, business_name, business_phone_number, website,
                   business_email, business_profile, business_address, business_state, business_city, business_status
            FROM businesses
            WHERE business_id = :id AND business_type IN ('rent_business', 'rental_business')
            LIMIT 1
        ");

            $stmt->bindParam(':id', $businessId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Get Single Rental Business Error: " . $e->getMessage());
            return null;
        }
    }

    public function getBusinessOwnerId(int $businessId): ?int
    {
        try {
            $pdo = Connection::connect();
            $stmt = $pdo->prepare("
                SELECT user_id
                FROM businesses
                WHERE business_id = :business_id
                  AND business_type IN ('rent_business', 'rental_business')
                LIMIT 1
            ");
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['user_id'] : null;
        } catch (PDOException $e) {
            error_log("Get Business Owner Error: " . $e->getMessage());
            return null;
        }
    }

    public function updateRentalBusiness(int $businessId, array $data): bool
    {
        try {
            $pdo = Connection::connect();

            $stmt = $pdo->prepare("
            UPDATE businesses SET
                business_name = :business_name,
                business_phone_number = :business_phone_number,
                website = :website,
                business_email = :business_email,
                business_profile = :business_profile,
                business_address = :business_address,
                business_state = :business_state,
                business_city = :business_city,
                business_status = :business_status
            WHERE business_id = :business_id AND business_type IN ('rent_business', 'rental_business')
        ");

            $stmt->bindParam(':business_name', $data['business_name']);
            $stmt->bindParam(':business_phone_number', $data['business_phone_number']);
            $stmt->bindParam(':website', $data['website']);
            $stmt->bindParam(':business_email', $data['business_email']);
            $stmt->bindParam(':business_profile', $data['business_profile']);
            $stmt->bindParam(':business_address', $data['business_address']);
            $stmt->bindParam(':business_state', $data['business_state']);
            $stmt->bindParam(':business_city', $data['business_city']);
            $stmt->bindParam(':business_status', $data['business_status']);
            $stmt->bindParam(':business_id', $businessId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update Rental Business Error: " . $e->getMessage());
            return false;
        }
    }
}