DROP PROCEDURE IF EXISTS `supla_delete_calcfg_queue`;

CREATE PROCEDURE `supla_delete_calcfg_queue`(IN `_user_id` INT, IN `_iodevice_id` INT)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
DELETE FROM `supla_calcfg_queue`
WHERE `user_id` = _user_id
  AND `iodevice_id` = _iodevice_id;
