DROP PROCEDURE IF EXISTS `supla_update_calcfg_queue`;

CREATE PROCEDURE `supla_update_calcfg_queue`(
    IN `_user_id` INT,
    IN `_iodevice_id` INT,
    IN `_queue` LONGTEXT CHARSET utf8mb4,
    IN `_valid_until` DATETIME
)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
INSERT INTO `supla_calcfg_queue`
    (`user_id`, `iodevice_id`, `queue`, `valid_until`, `updated_at`)
VALUES
    (_user_id, _iodevice_id, _queue, _valid_until, UTC_TIMESTAMP())
ON DUPLICATE KEY UPDATE
    `user_id` = _user_id,
    `queue` = _queue,
    `valid_until` = _valid_until,
    `updated_at` = UTC_TIMESTAMP();
