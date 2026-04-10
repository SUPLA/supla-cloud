DROP PROCEDURE IF EXISTS `supla_update_channel_functions`;

CREATE PROCEDURE `supla_update_channel_functions`(IN `_channel_id` INT, IN `_user_id` INT, IN `_flist` INT)
    MODIFIES SQL DATA
UPDATE supla_dev_channel
SET flist = IFNULL(flist, 0) | IFNULL(_flist, 0)
WHERE id = _channel_id
  AND user_id = _user_id;
