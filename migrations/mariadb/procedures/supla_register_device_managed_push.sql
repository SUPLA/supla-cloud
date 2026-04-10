DROP PROCEDURE IF EXISTS `supla_register_device_managed_push`;

CREATE PROCEDURE `supla_register_device_managed_push`(IN `_user_id` INT, IN `_device_id` INT, IN `_channel_id` INT, IN `_sm_title` TINYINT,
                                                      IN `_sm_body` TINYINT, IN `_sm_sound` TINYINT)
INSERT INTO `supla_push_notification`(`user_id`,
                                      `iodevice_id`,
                                      `channel_id`,
                                      `managed_by_device`,
                                      `title`,
                                      `body`,
                                      `sound`)
SELECT _user_id,
       _device_id,
       CASE _channel_id
           WHEN 0 THEN NULL
           ELSE _channel_id END,
       1,
       CASE _sm_title WHEN 0 THEN NULL ELSE '' END,
       CASE _sm_body WHEN 0 THEN NULL ELSE '' END,
       CASE _sm_sound WHEN 0 THEN NULL ELSE 0 END
FROM DUAL
WHERE NOT EXISTS(SELECT id
                 FROM `supla_push_notification`
                 WHERE user_id = _user_id
                   AND iodevice_id = _device_id
                   AND managed_by_device = 1
                   AND ((_channel_id = 0 AND channel_id IS NULL)
                     OR (channel_id != 0 AND channel_id =
                                             _channel_id))
                 LIMIT 1)
