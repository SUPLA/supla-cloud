DROP PROCEDURE IF EXISTS `supla_set_location_caption`;

CREATE PROCEDURE `supla_set_location_caption`(
    IN `_user_id` INT,
    IN `_location_id` INT,
    IN `_caption` VARCHAR(100) CHARSET utf8mb4
) NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
UPDATE
    supla_location
SET
    caption = _caption
WHERE
    id = _location_id AND user_id = _user_id;
SELECT ROW_COUNT();
END