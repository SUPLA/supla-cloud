DROP PROCEDURE IF EXISTS `supla_set_channel_json_config`;

CREATE PROCEDURE `supla_set_channel_json_config`(IN `_user_id` INT, IN `_channel_id` INT, IN `_user_config` TEXT CHARSET utf8mb4,
                                                 IN `_user_config_md5` VARCHAR(32), IN `_properties` TEXT CHARSET utf8mb4,
                                                 IN `_properties_md5` VARCHAR(32))
    NOT DETERMINISTIC
    MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
    UPDATE supla_dev_channel
    SET user_config = _user_config,
        properties  = _properties
    WHERE id = _channel_id
      AND user_id = _user_id
      AND MD5(IFNULL(user_config, '')) = _user_config_md5
      AND MD5(IFNULL(properties, '')) = _properties_md5;
    SELECT ABS(STRCMP(user_config, _user_config COLLATE utf8mb4_unicode_ci)) + ABS(STRCMP(properties, _properties COLLATE utf8mb4_unicode_ci))
    FROM supla_dev_channel
    WHERE id = _channel_id
      AND user_id = _user_id;
END
