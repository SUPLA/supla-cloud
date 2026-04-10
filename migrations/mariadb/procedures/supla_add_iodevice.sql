DROP PROCEDURE IF EXISTS `supla_add_iodevice`;

CREATE PROCEDURE `supla_add_iodevice`(IN `_location_id` INT(11), IN `_user_id` INT(11), IN `_guid` VARBINARY(16),
                                      IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_reg_ipv4` INT(10) UNSIGNED,
                                      IN `_software_version` VARCHAR(20), IN `_protocol_version` INT(11), IN `_product_id` SMALLINT,
                                      IN `_manufacturer_id` SMALLINT, IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64),
                                      IN `_flags` INT(11), OUT `_id` INT(11))
    NOT DETERMINISTIC
    MODIFIES SQL DATA
BEGIN
    SET
        @mfr_id = _manufacturer_id;
    IF
        _manufacturer_id = 0 THEN
        IF _name LIKE '%sonoff%' THEN
            SELECT 6
            INTO @mfr_id;
        END IF;
        IF
            _name LIKE 'NICE %' THEN
            SELECT 5
            INTO @mfr_id;
        END IF;
        IF
            _name LIKE 'ZAMEL %' THEN
            SELECT 4
            INTO @mfr_id;
        END IF;
    END IF;
    INSERT INTO `supla_iodevice`(`location_id`, `user_id`, `guid`, `name`, `enabled`, `reg_date`, `reg_ipv4`, `last_connected`, `last_ipv4`,
                                 `software_version`, `protocol_version`, `manufacturer_id`, `product_id`, `original_location_id`,
                                 `auth_key`,
                                 `flags`)
    VALUES (_location_id, _user_id, _guid, _name, 1, UTC_TIMESTAMP(), _reg_ipv4, UTC_TIMESTAMP(), _reg_ipv4, _software_version,
            _protocol_version, @mfr_id, _product_id, _original_location_id, _auth_key, _flags);
    SELECT LAST_INSERT_ID()
    INTO _id;
    SELECT CONCAT('{"template": "new_io_device", "userId": ', _user_id, ', "data": {"ioDeviceId": ', _id, '}}')
    INTO @notification_data;
    INSERT INTO `supla_email_notifications` (`body`, `headers`, `queue_name`, `created_at`, `available_at`)
    VALUES (@notification_data, '[]', 'supla-server', NOW(), NOW());
END;
