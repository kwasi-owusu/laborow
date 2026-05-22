<?php

require_once dirname(__DIR__, 2) . '/template/statics/conn/anthrax.php';

class UserModel
{
    public static function addUser($table_a, $table_b, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO $table_a (
                first_name, last_name, email, user_phone_number, password,
                user_type, user_access_level, country_code, state_region, city, verification_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $data['fn'],
                $data['ln'],
                $data['em'],
                $data['phn'],
                $data['pd'],
                $data['ust'],
                $data['rl'],
                $data['ctr'],
                $data['stt'],
                $data['cty'],
                $data['verification_code']
            ]);

            $user_id = $pdo->lastInsertId();

            // Log activity
            self::logActivity($pdo, "New User Added", "New user added with ID " . $pdo->lastInsertId());

            if ($data['ust'] !== 'individual') {
                self::setup_business($pdo, $user_id, $data);
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Add User Error: " . $e->getMessage());
            return false;
        }
    }

    public static function addRole($tbl, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO $tbl(roleName, role_desc, AddedBy) VALUES (?, ?, ?)");
            $stmt->execute([$data['rn'], $data['rd'], $data['adb']]);

            self::logActivity($pdo, "New User Role Added", "Role ID " . $pdo->lastInsertId());

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Add Role Error: " . $e->getMessage());
            return false;
        }
    }

    public static function changeUserRole($tbl, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE $tbl SET userRole = :ur, lastUpdateOn = :ln, lastUpdateBy = :lb WHERE user_ID = :uid");
            $stmt->execute([
                'ur' => $data['ur'],
                'ln' => $data['ln'],
                'lb' => $data['lb'],
                'uid' => $data['ud'],
            ]);

            self::logActivity($pdo, "User Role Updated", "Role updated for user ID " . $data['ud']);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Change User Role Error: " . $e->getMessage());
            return false;
        }
    }

    public static function editUserDetails($tbl, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE $tbl SET 
                first_name = :fn, last_name = :ln, email = :em,
                user_phone_number = :phn, user_type = :rl, 
                last_update_on = :nn, last_update_by = :lbd 
                WHERE user_id = :uid");

            $stmt->execute([
                'fn' => $data['fn'],
                'ln' => $data['ln'],
                'em' => $data['em'],
                'phn' => $data['phn'],
                'rl' => $data['rl'],
                'nn' => $data['nn'],
                'lbd' => $data['lbd'],
                'uid' => $data['ud'],
            ]);

            self::logActivity($pdo, "User Details Updated", "Details updated for user ID " . $data['ud']);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Edit User Details Error: " . $e->getMessage());
            return false;
        }
    }

    public static function updateUserStatus($tbl, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE $tbl SET UserStatus = :ust, lastUpdateOn = :lbn, lastUpdateBy = :lbd WHERE user_ID = :ud");
            $stmt->execute([
                'ust' => $data['ust'],
                'lbn' => $data['lbn'],
                'lbd' => $data['lbd'],
                'ud' => $data['ud'],
            ]);

            // Update sales_persons table if needed
            if ((int)$data['ust'] === 2) {
                $sps = $pdo->prepare("UPDATE sales_persons SET sales_person_status = :st WHERE sales_person = :sp");
                $sps->execute(['st' => $data['ust'], 'sp' => $data['ud']]);
            }

            self::logActivity($pdo, "User Status Updated", "Status updated for user ID " . $data['ud']);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Update User Status Error: " . $e->getMessage());
            return false;
        }
    }

    public static function updateUserPwd($tbl, $data): bool
    {
        $pdo = (new Connection())->Connect();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE $tbl SET password = :pd, user_phone_number = :phn, last_update_on = :lbn, last_update_by = :lbd 
            WHERE user_ID = :ud");
            $stmt->execute([
                'pd' => $data['npd'],
                'phn' => $data['phn'],
                'lbn' => $data['lbn'],
                'lbd' => $data['lbd'],
                'ud' => $data['ud'],
            ]);

            self::logActivity($pdo, "User Password Updated", "Password updated for user ID " . $data['ud']);

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Update User Password Error: " . $e->getMessage());
            return false;
        }
    }

    private static function logActivity(PDO $pdo, string $type, string $details): void
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_activities (activity_module, activity_desc) VALUES (?, ?)");
            $stmt->execute([$type, $details]);
        } catch (PDOException $e) {
            error_log("Activity Log Error: " . $e->getMessage());
        }
    }

    public static function setup_business(PDO $pdo, $user_id, $data)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO businesses(user_id, business_type, business_name, business_phone_number, 
            website, business_email, business_address, business_state, business_city)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(
                [
                    $user_id,
                    $data['ust'],
                    $data['business_name'],
                    $data['business_phone_number'],
                    $data['website'],
                    $data['business_email'],
                    $data['business_address'],
                    $data['business_state'],
                    $data['business_city']
                ]
            );
        } catch (PDOException $e) {
            error_log("Business Setup Error: " . $e->getMessage());
        }
    }

    public function update_device_token($data): bool
    {
        try {
            $pdo = (new Connection())->Connect();
            $stmt = $pdo->prepare("UPDATE users SET current_device_token = :dt, last_update_on = NOW() WHERE user_id = :id");
            $stmt->bindValue(':dt', $data['d_tkn'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $data['uid'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            // Optionally log the error
            error_log("Device token update error: " . $e->getMessage());
            return false;
        }
    }
}
