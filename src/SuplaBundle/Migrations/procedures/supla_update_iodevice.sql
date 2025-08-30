DROP PROCEDURE IF EXISTS `supla_update_iodevice`;

CREATE PROCEDURE `supla_update_iodevice`(IN `_name` VARCHAR(100) CHARSET utf8mb4, IN `_last_ipv4` INT(10) UNSIGNED,
                                         IN `_software_version` VARCHAR(20) CHARSET utf8, IN `_protocol_version` INT(11),
                                         IN `_original_location_id` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11),
                                         IN `_flags` INT(11))
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
    UPDATE `supla_iodevice`
      SET `name`                   = _name,
          `last_connected`         = UTC_TIMESTAMP(),
          `last_ipv4`              = _last_ipv4,
          `software_version`       = _software_version,
          `protocol_version`       = _protocol_version,
          original_location_id     = _original_location_id,
          `flags`                  = IFNULL(`flags`, 0) | IFNULL(_flags, 0),
          channel_addition_blocked = 0
      WHERE `id` = _id;

    SET @user_id = NULL;
    SET @properies = NULL;
    SET @user_config = NULL;

    SELECT user_id,
           user_config, properties
      INTO @user_id, @user_config, @properties
      FROM `supla_iodevice` WHERE id = _id
          AND JSON_VALUE(properties, '$.otaUpdate.version') = _software_version;

    IF @user_id IS NOT NULL THEN
        CALL supla_set_device_json_config(@user_id, _id,
                                              @user_config,
                                              MD5(IFNULL(@user_config, '')),
                                              JSON_REMOVE(@properties, '$.otaUpdate'),
                                              MD5(IFNULL(@properties, '')));

    END IF;

    IF _auth_key IS NOT NULL THEN
      UPDATE `supla_iodevice`
      SET `auth_key` = _auth_key
            WHERE `id` = _id
              AND `auth_key` IS NULL;
    END IF;

    UPDATE `supla_dev_channel`
      SET conflict_details = NULL
      WHERE `iodevice_id` = _id
            AND conflict_details IS NOT NULL;
END
