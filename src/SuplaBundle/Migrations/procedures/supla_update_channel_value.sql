DROP PROCEDURE IF EXISTS `supla_update_channel_value`;

CREATE PROCEDURE `supla_update_channel_value`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_value` VARBINARY(8),
    IN `_validity_time_sec` INT
)
    NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN
    IF _validity_time_sec > 0 THEN
        SET @valid_to = DATE_ADD(UTC_TIMESTAMP(), INTERVAL _validity_time_sec SECOND);
    END IF;

    UPDATE `supla_dev_channel_value`
    SET `update_time` = UTC_TIMESTAMP(),
        `valid_to`    = @valid_to,
        `value`       = _value
    WHERE user_id = _user_id
      AND channel_id = _id;

    IF ROW_COUNT() = 0 THEN
        INSERT INTO `supla_dev_channel_value` (`channel_id`, `user_id`, `update_time`, `valid_to`, `value`)
        VALUES (_id, _user_id, UTC_TIMESTAMP(), @valid_to, _value)
        ON DUPLICATE KEY UPDATE `value` = _value, update_time = UTC_TIMESTAMP(), valid_to = @valid_to;
    END IF;
END
