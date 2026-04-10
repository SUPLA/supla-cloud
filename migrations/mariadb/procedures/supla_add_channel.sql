DROP PROCEDURE IF EXISTS `supla_add_channel`;

CREATE PROCEDURE `supla_add_channel`(IN `_type` INT, IN `_func` INT, IN `_param1` INT, IN `_param2` INT, IN `_param3` INT,
                                     IN `_user_id` INT, IN `_channel_number` INT, IN `_iodevice_id` INT, IN `_flist` INT, IN `_flags` BIGINT,
                                     IN `_alt_icon` INT, IN `_sub_device_id` SMALLINT UNSIGNED)
    NOT DETERMINISTIC
    MODIFIES SQL DATA
    SQL SECURITY DEFINER
BEGIN
    INSERT INTO `supla_dev_channel` (`type`, `func`, `param1`, `param2`, `param3`, `user_id`, `channel_number`, `iodevice_id`, `flist`,
                                     `flags`, `alt_icon`, `sub_device_id`)
    VALUES (_type, _func, _param1, _param2, _param3, _user_id, _channel_number, _iodevice_id, _flist, _flags, _alt_icon, _sub_device_id);
END
