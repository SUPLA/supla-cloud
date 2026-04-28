DROP PROCEDURE IF EXISTS `supla_set_channel_group_caption`;

CREATE PROCEDURE `supla_set_channel_group_caption`(
    IN `_user_id` INT,
    IN `_channel_group_id` INT,
    IN `_caption` VARCHAR(255) CHARSET utf8mb4
) NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
UPDATE
    supla_dev_channel_group
SET
    caption = _caption
WHERE
    id = _channel_group_id AND user_id = _user_id;
SELECT ROW_COUNT();
END