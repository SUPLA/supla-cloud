DROP PROCEDURE IF EXISTS `supla_set_channel_caption`;

CREATE PROCEDURE `supla_set_channel_caption`(IN `_user_id` INT, IN `_channel_id` INT, IN `_caption` VARCHAR(100) CHARSET utf8mb4,
                                             IN `_only_when_not_null` BIT)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
UPDATE supla_dev_channel
SET caption = _caption
WHERE id = _channel_id
  AND user_id = _user_id
  AND (caption IS NULL OR _only_when_not_null = 0);
