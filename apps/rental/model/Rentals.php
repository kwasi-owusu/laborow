<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class Rentals
{
    public static function request_to_rent(array $data): bool
    {
        try {
            $pdo = (new Connection())->Connect();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO rent_requests (
                    user_ID, request_item, request_description, priority, date_needed
                ) VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['user_ID'],
                trim($data['request_item']),
                trim($data['request_description']),
                trim($data['priority']),
                $data['expected_date']
            ]);

            $listingID = $pdo->lastInsertId();

            if (!empty($data['imgs']) && is_array($data['imgs'])) {
                $imgStmt = $pdo->prepare("
                    INSERT INTO rent_request_images (rent_req_id, asset_image_save)
                    VALUES (?, ?)
                ");

                foreach ($data['imgs'] as $image) {
                    $mimeType = strtolower($image['mime_type']);
                    $base64 = $image['base64'];

                    if (!in_array($mimeType, ['jpeg', 'png'], true)) {
                        throw new RuntimeException("Invalid MIME type: {$mimeType}");
                    }

                    // Store base64 safely
                    $imageData = "data:image/{$mimeType};base64,{$base64}";
                    $imgStmt->execute([$listingID, $imageData]);
                }
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('[request_to_rent] Error: ' . $e->getMessage());
            return false;
        }
    }


    public function get_asset_rental_details(int $assetId): ?array
    {
        try {
            $pdo = (new Connection())->Connect();
            $stmt = $pdo->prepare("
                SELECT assets_ID, user_ID, rent_amount, charge_type, asset_status
                FROM assets
                WHERE assets_ID = :asset_id
                LIMIT 1
            ");
            $stmt->bindParam(':asset_id', $assetId, PDO::PARAM_INT);
            $stmt->execute();

            $asset = $stmt->fetch(PDO::FETCH_ASSOC);
            return $asset ?: null;
        } catch (PDOException $e) {
            error_log('[get_asset_rental_details] Error: ' . $e->getMessage());
            return null;
        }
    }
    public function get_rent_participants(int $rentId): ?array
    {
        try {
            $pdo = (new Connection())->Connect();
            $stmt = $pdo->prepare("
                SELECT current_rent_ID, user_ID, asset_owner_ID, asset_id
                FROM rent
                WHERE current_rent_ID = :rent_id
                LIMIT 1
            ");
            $stmt->bindParam(':rent_id', $rentId, PDO::PARAM_INT);
            $stmt->execute();

            $rent = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rent ?: null;
        } catch (PDOException $e) {
            error_log('[get_rent_participants] Error: ' . $e->getMessage());
            return null;
        }
    }
    public function rent_this(array $data): bool
    {
        try {
            $pdo = (new Connection())->Connect();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
            INSERT INTO rent (
                asset_id, user_ID, total_duration, charge_type,
                start_date, return_date, total_amt, asset_owner_ID,
                payment_method, payment_ref
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

            $stmt->execute([
                $data['asset_id'],
                $data['user_ID'],
                $data['total_duration'],
                $data['charge_type'],
                $data['start_date'],
                $data['return_date'],
                $data['total_amt'],
                $data['asset_owner_ID'],
                $data['payment_method'],
                $data['payment_ref']
            ]);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('[rent_this] Error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_rent_payment_details(int $assetId, string $reference): ?array
    {
        try {
            $pdo = (new Connection())->Connect();
            $stmt = $pdo->prepare("
                SELECT current_rent_ID, asset_id, payment_ref, total_amt, pmt_status
                FROM rent
                WHERE asset_id = :asset_id AND payment_ref = :reference
                LIMIT 1
            ");
            $stmt->execute([
                ':asset_id' => $assetId,
                ':reference' => $reference
            ]);

            $rent = $stmt->fetch(PDO::FETCH_ASSOC);
            return $rent ?: null;
        } catch (Throwable $e) {
            error_log('[get_rent_payment_details] Error: ' . $e->getMessage());
            return null;
        }
    }
    public function update_rent_pmt_status(array $data): bool
    {
        try {
            $pdo = (new Connection())->Connect();
            $pdo->beginTransaction();

            $incomingStatus = strtolower(trim((string)($data['pmt_status'] ?? '')));
            $paymentConfirmed = in_array($incomingStatus, ['success', 'paid'], true);
            $storedStatus = $paymentConfirmed ? 'paid' : $incomingStatus;

            $rentExists = $pdo->prepare("
                SELECT current_rent_ID
                FROM rent
                WHERE asset_id = :id AND payment_ref = :ref
                LIMIT 1
            ");
            $rentExists->execute([
                ':id'  => $data['asset_id'],
                ':ref' => $data['reference']
            ]);

            if (!$rentExists->fetch(PDO::FETCH_ASSOC)) {
                $pdo->rollBack();
                return false;
            }

            $stmt = $pdo->prepare("
                UPDATE rent
                SET pmt_status = :pmt_st, payment_method = :mtd
                WHERE asset_id = :id AND payment_ref = :ref
            ");

            $stmt->execute([
                ':pmt_st' => $storedStatus,
                ':mtd'    => $data['payment_method'],
                ':id'     => $data['asset_id'],
                ':ref'    => $data['reference']
            ]);

            if ($paymentConfirmed) {
                $updateAsset = $pdo->prepare("
                    UPDATE assets SET asset_status = 'rented' WHERE assets_ID = :d
                ");
                $updateAsset->execute([':d' => $data['asset_id']]);
            }

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('[update_rent_pmt_status] Error: ' . $e->getMessage());
            return false;
        }
    }

    public function mark_asset_as_returned($current_rent_id, $actual_return_date, $return_notes = null, $return_confirmation_photo = null)
    {
        $pdo = (new Connection())->Connect();
        $pdo->beginTransaction();

        error_log('[mark_asset_as_returned] Updating rent_id=' . (int)$current_rent_id);

        $sql = "UPDATE rent
        SET is_returned = 1,
            actual_return_date = :actual_return_date,
            return_notes = :return_notes,
            return_confirmation_photo = :return_confirmation_photo,
            last_update_on = CURRENT_TIMESTAMP
        WHERE current_rent_ID = :current_rent_id
          AND receipt_confirmed_by_renter = 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':actual_return_date', $actual_return_date);
        $stmt->bindParam(':return_notes', $return_notes);
        $stmt->bindParam(':return_confirmation_photo', $return_confirmation_photo);
        $stmt->bindParam(':current_rent_id', $current_rent_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            $pdo->rollBack();
            error_log("SQL Execution failed: " . implode(", ", $stmt->errorInfo()));
            return ['status' => false, 'message' => 'Failed to mark asset as returned.'];
        }

        if ($stmt->rowCount() < 1) {
            $pdo->rollBack();
            return ['status' => false, 'message' => 'Rental not found, receipt not confirmed, or return was not updated.'];
        }

        $q = $pdo->prepare("SELECT a.asset_title
            FROM rent r JOIN assets a ON r.asset_id = a.assets_ID
            WHERE r.current_rent_ID = :id");
        $q->bindParam(':id', $current_rent_id, PDO::PARAM_INT);
        $q->execute();
        $asset = $q->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        return [
            'status' => true,
            'message' => 'Asset marked as returned.',
            'asset_title' => $asset['asset_title'] ?? ''
        ];
    }

    public function confirm_asset_return_by_owner($current_rent_id, $owner_id)
    {
        $pdo = (new Connection())->Connect();
        $pdo->beginTransaction();

        error_log('[confirm_asset_return_by_owner] Updating rent_id=' . (int)$current_rent_id . ', owner_id=' . (int)$owner_id);

        $sql = "UPDATE rent
            SET return_confirmed_by_owner = 1,
                rent_status = 'completed',
                last_update_on = CURRENT_TIMESTAMP
            WHERE current_rent_ID = :current_rent_id
              AND asset_owner_ID = :owner_id
              AND is_returned = 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':current_rent_id', $current_rent_id, PDO::PARAM_INT);
        $stmt->bindParam(':owner_id', $owner_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            $pdo->rollBack();
            return ['status' => false, 'message' => 'Failed to confirm return by owner.'];
        }

        if ($stmt->rowCount() < 1) {
            $pdo->rollBack();
            return ['status' => false, 'message' => 'Rental not found, asset not returned, or owner could not confirm this return.'];
        }

        $pdo->commit();
        return ['status' => true, 'message' => 'Return confirmed by owner. Rental closed.'];
    }

    public function get_owner_device_token($rentId)
    {
        try {
            $pdo = (new Connection())->Connect();

            $stmt = $pdo->prepare("
            SELECT u.current_device_token
            FROM rent r
            JOIN users u ON r.asset_owner_ID = u.user_id
            WHERE r.current_rent_ID = :rent_id
            LIMIT 1
        ");
            $stmt->bindParam(':rent_id', $rentId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['current_device_token' => null];
        } catch (PDOException $e) {
            error_log("get_owner_device_token() failed: " . $e->getMessage());
            return ['current_device_token' => null];
        }
    }

    public function mark_asset_as_issued($rent_id, $notes = null, $photo_url = null)
    {
        $pdo = (new Connection())->Connect();

        $sql = "UPDATE rent SET
                is_issued_by_owner = 1,
                issue_notes = :notes,
                issue_photo = :photo,
                issue_timestamp = NOW(),
                last_update_on = NOW()
            WHERE current_rent_ID = :rent_id
              AND LOWER(COALESCE(pmt_status, '')) = 'paid'
              AND LOWER(COALESCE(rent_status, 'approved')) NOT IN ('completed', 'cancelled', 'canceled', 'rejected')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':photo', $photo_url);
        $stmt->bindParam(':rent_id', $rent_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return ['status' => false, 'message' => 'Failed to mark as issued.'];
        }

        if ($stmt->rowCount() < 1) {
            return ['status' => false, 'message' => 'Rental not found, payment not confirmed, rental not approved, or issue was not updated.'];
        }

        return ['status' => true, 'message' => 'Asset marked as issued.'];
    }

    public function confirm_asset_receipt_by_renter($rent_id)
    {
        $pdo = (new Connection())->Connect();

        $sql = "UPDATE rent SET
                receipt_confirmed_by_renter = 1,
                receipt_confirmation_timestamp = NOW(),
                last_update_on = NOW()
            WHERE current_rent_ID = :rent_id
              AND is_issued_by_owner = 1";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':rent_id', $rent_id, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return ['status' => false, 'message' => 'Failed to confirm receipt.'];
        }

        if ($stmt->rowCount() < 1) {
            return ['status' => false, 'message' => 'Rental not found, asset not issued, or receipt was not updated.'];
        }

        return ['status' => true, 'message' => 'Receipt confirmed by renter.'];
    }
}
