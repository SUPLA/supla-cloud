DROP PROCEDURE IF EXISTS `supla_set_scene_caption`;

CREATE PROCEDURE `supla_set_scene_caption`(
    IN `_user_id` INT,
    IN `_scene_id` INT,
    IN `_caption` VARCHAR(255) CHARSET utf8mb4
) NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
UPDATE
    supla_scene
SET
    caption = _caption
WHERE
    id = _scene_id AND user_id = _user_id;
SELECT ROW_COUNT();
END