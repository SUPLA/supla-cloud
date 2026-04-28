DROP PROCEDURE IF EXISTS `supla_update_channel_extended_value`;

CREATE PROCEDURE `supla_update_channel_extended_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_type` TINYINT,
    IN `_value` VARBINARY(1024)
)
    NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    UPDATE `supla_dev_channel_extended_value`
    SET `update_time` = UTC_TIMESTAMP(),
        `type`        = _type,
        `value`       = _value
    WHERE user_id = _user_id
      AND channel_id = _id;

    IF ROW_COUNT() = 0 THEN
        INSERT INTO `supla_dev_channel_extended_value` (`channel_id`, `user_id`, `update_time`, `type`, `value`)
        VALUES (_id, _user_id, UTC_TIMESTAMP(), _type, _value)
        ON DUPLICATE KEY UPDATE `type` = _type, `value` = _value, `update_time` = UTC_TIMESTAMP();
    END IF;
END
