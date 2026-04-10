DROP PROCEDURE IF EXISTS `supla_update_channel_flags`;

CREATE PROCEDURE `supla_update_channel_flags`(
    IN `_channel_id` INT,
    IN `_user_id` INT,
    IN `_flags` BIGINT
) NO SQL
UPDATE
    supla_dev_channel
SET
    flags = IFNULL(flags, 0) | IFNULL(_flags, 0)
WHERE
    id = _channel_id AND user_id = _user_id;