DROP PROCEDURE IF EXISTS `supla_set_device_json_config`;


CREATE PROCEDURE `supla_set_device_json_config`(IN `_user_id` INT, IN `_device_id` INT, IN `_user_config` VARCHAR(4096),
                                                IN `_user_config_md5` VARCHAR(32), IN `_properties` VARCHAR(2048),
                                                IN `_properties_md5` VARCHAR(32))
    NOT DETERMINISTIC
    MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
    UPDATE supla_iodevice
    SET user_config = _user_config,
        properties  = _properties
    WHERE id = _device_id
      AND user_id = _user_id
      AND MD5(IFNULL(user_config, '')) = _user_config_md5
      AND MD5(IFNULL(properties, '')) = _properties_md5;
    SELECT ABS(STRCMP(user_config, _user_config)) + ABS(STRCMP(properties, _properties))
    FROM supla_iodevice
    WHERE id = _device_id
      AND user_id = _user_id;
END
