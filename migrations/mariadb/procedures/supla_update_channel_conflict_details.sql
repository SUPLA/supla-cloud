DROP PROCEDURE IF EXISTS `supla_update_channel_conflict_details`;

CREATE PROCEDURE `supla_update_channel_conflict_details`(IN `_iodevice_id` INT, IN `_channel_number` INT,
                                                         IN `_details` VARCHAR(256) CHARSET utf8mb4)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
UPDATE `supla_dev_channel`
SET `conflict_details` = _details
WHERE `iodevice_id` = _iodevice_id
  AND `channel_number` = _channel_number;
