DROP PROCEDURE IF EXISTS `supla_update_channel_state`;

CREATE PROCEDURE `supla_update_channel_state`(
    IN `_id` INT,
    IN `_user_id` INT,
    IN `_state` TEXT CHARSET utf8mb4
) NOT DETERMINISTIC NO SQL SQL SECURITY DEFINER
BEGIN

UPDATE `supla_dev_channel_state` SET `update_time` = UTC_TIMESTAMP(), `state` = _state
         WHERE user_id = _user_id AND channel_id = _id;

IF ROW_COUNT() = 0 THEN
      INSERT INTO `supla_dev_channel_state` (`channel_id`, `user_id`, `update_time`, `state`)
         VALUES(_id, _user_id, UTC_TIMESTAMP(), _state)
      ON DUPLICATE KEY UPDATE `state` = _state, update_time = UTC_TIMESTAMP();
END IF;
END