<?php 
require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';
class FetchMyRentals{

    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Connection())->Connect();
    }

    private function baseSelectRentRequestQuery(): string
    {
        return "SELECT rent_requests.*, rent_request_images.*
            FROM rent_requests
            LEFT JOIN rent_request_images ON rent_requests.request_ID = rent_request_images.rent_req_id";
    }

    public function fetchMyRentRequest($user_ID): array
    {
        $query = $this->baseSelectRentRequestQuery() . " WHERE rent_requests.user_ID = :u";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':u', $user_ID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchMyPendingRentRequest($user_ID): array
    {
        $query = $this->baseSelectRentRequestQuery() . " WHERE rent_requests.user_ID = :u AND request_status = 'Pending'";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':u', $user_ID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




}