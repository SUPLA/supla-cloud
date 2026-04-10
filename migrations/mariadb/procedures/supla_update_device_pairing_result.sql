DROP PROCEDURE IF EXISTS `supla_update_device_pairing_result`;

CREATE PROCEDURE `supla_update_device_pairing_result`(IN `_iodevice_id` INT, IN `_pairing_result` VARCHAR(512) CHARSET utf8mb4)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
UPDATE `supla_iodevice`
SET `pairing_result` = _pairing_result
WHERE `id` = _iodevice_id;
