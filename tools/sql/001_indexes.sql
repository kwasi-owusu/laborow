-- Tuetra/Laborow MySQL index migration
-- Safe to re-run. It only adds indexes when the target table, columns, and index do not already exist.

DELIMITER $$

DROP PROCEDURE IF EXISTS laborow_add_index_if_missing $$
CREATE PROCEDURE laborow_add_index_if_missing(
    IN p_table_name VARCHAR(64),
    IN p_index_name VARCHAR(64),
    IN p_required_columns VARCHAR(255),
    IN p_index_columns VARCHAR(512)
)
BEGIN
    DECLARE v_table_exists INT DEFAULT 0;
    DECLARE v_index_exists INT DEFAULT 0;
    DECLARE v_required_count INT DEFAULT 0;
    DECLARE v_existing_required_count INT DEFAULT 0;
    DECLARE v_sql TEXT;

    SELECT COUNT(*) INTO v_table_exists
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = p_table_name;

    IF v_table_exists > 0 THEN
        SELECT COUNT(*) INTO v_index_exists
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND INDEX_NAME = p_index_name;

        SELECT 1 + LENGTH(p_required_columns) - LENGTH(REPLACE(p_required_columns, ',', '')) INTO v_required_count;

        SELECT COUNT(*) INTO v_existing_required_count
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = p_table_name
          AND FIND_IN_SET(COLUMN_NAME, p_required_columns) > 0;

        IF v_index_exists = 0 AND v_existing_required_count = v_required_count THEN
            SET v_sql = CONCAT('CREATE INDEX `', p_index_name, '` ON `', p_table_name, '` (', p_index_columns, ')');
            SET @laborow_sql = v_sql;
            PREPARE laborow_stmt FROM @laborow_sql;
            EXECUTE laborow_stmt;
            DEALLOCATE PREPARE laborow_stmt;
        END IF;
    END IF;
END $$

DELIMITER ;

-- Auth/account lookups
-- Long TEXT/VARCHAR columns use prefix indexes to stay under MySQL/InnoDB key-length limits.
CALL laborow_add_index_if_missing('users', 'idx_users_email', 'email', '`email`');
CALL laborow_add_index_if_missing('users', 'idx_users_verification_code', 'verification_code', '`verification_code`');
CALL laborow_add_index_if_missing('users', 'idx_users_status', 'userStatus', '`userStatus`');
CALL laborow_add_index_if_missing('users', 'idx_users_device_token', 'current_device_token', '`current_device_token`(191)');
CALL laborow_add_index_if_missing('password_logs', 'idx_password_logs_user_password', 'user_id,password', '`user_id`, `password`(191)');
CALL laborow_add_index_if_missing('password_logs', 'idx_password_logs_user_date', 'user_id,system_date', '`user_id`, `system_date`');

-- Listing reads and owner views
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_date', 'asset_status,system_date', '`asset_status`, `system_date`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_category', 'asset_status,category_ID', '`asset_status`, `category_ID`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_sub_category', 'asset_status,sub_category_ID', '`asset_status`, `sub_category_ID`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_slug', 'asset_status,asset_slug', '`asset_status`, `asset_slug`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_user', 'asset_status,user_ID', '`asset_status`, `user_ID`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_featured_status', 'is_featured,asset_status', '`is_featured`, `asset_status`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_rent_count', 'asset_status,total_rent_count', '`asset_status`, `total_rent_count`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_status_view_count', 'asset_status,total_view_count', '`asset_status`, `total_view_count`');
CALL laborow_add_index_if_missing('assets', 'idx_assets_user', 'user_ID', '`user_ID`');
CALL laborow_add_index_if_missing('asset_images', 'idx_asset_images_asset_id', 'asset_ID', '`asset_ID`');
CALL laborow_add_index_if_missing('businesses', 'idx_businesses_user_id', 'user_id', '`user_id`');

-- Rental writes, payment verification, and lifecycle views
CALL laborow_add_index_if_missing('rent', 'idx_rent_asset_payment_ref', 'asset_id,payment_ref', '`asset_id`, `payment_ref`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_user', 'user_ID', '`user_ID`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_owner', 'asset_owner_ID', '`asset_owner_ID`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_asset', 'asset_id', '`asset_id`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_owner_returned', 'asset_owner_ID,is_returned', '`asset_owner_ID`, `is_returned`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_payment_status', 'pmt_status', '`pmt_status`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_status', 'rent_status', '`rent_status`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_user_date', 'user_ID,system_date', '`user_ID`, `system_date`');
CALL laborow_add_index_if_missing('rent', 'idx_rent_owner_date', 'asset_owner_ID,system_date', '`asset_owner_ID`, `system_date`');

-- Rental requests and request images
CALL laborow_add_index_if_missing('rent_requests', 'idx_rent_requests_user', 'user_ID', '`user_ID`');
CALL laborow_add_index_if_missing('rent_requests', 'idx_rent_requests_user_status', 'user_ID,request_status', '`user_ID`, `request_status`');
CALL laborow_add_index_if_missing('rent_request_images', 'idx_rent_request_images_rent_req_id', 'rent_req_id', '`rent_req_id`');

-- Activity/audit views
CALL laborow_add_index_if_missing('user_activities', 'idx_user_activities_user_date', 'user_id,system_date', '`user_id`, `system_date`');

DROP PROCEDURE IF EXISTS laborow_add_index_if_missing;
