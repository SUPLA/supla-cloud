-- SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
-- SPDX-License-Identifier: GPL-2.0-or-later

DROP PROCEDURE IF EXISTS `supla_touch_ocpp_channel_values`;

CREATE PROCEDURE `supla_touch_ocpp_channel_values`(
    IN `_device_id` INT,
    IN `_user_id` INT,
    IN `_validity_time_sec` INT UNSIGNED,
    IN `_new_connection` TINYINT UNSIGNED
)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
BEGIN
    IF _new_connection <> 0 THEN
        UPDATE `supla_iodevice` AS d
            JOIN `supla_ocpp_charging_station` AS s ON s.iodevice_id = d.id AND s.user_id = d.user_id
        SET d.last_connected = UTC_TIMESTAMP()
        WHERE s.iodevice_id = _device_id AND s.user_id = _user_id;
    END IF;

    UPDATE `supla_dev_channel_value` AS v
        JOIN `supla_dev_channel` AS c ON c.id = v.channel_id AND c.user_id = v.user_id
        JOIN `supla_ocpp_charging_station` AS s ON s.iodevice_id = c.iodevice_id AND s.user_id = c.user_id
    SET v.valid_to = TIMESTAMPADD(SECOND, _validity_time_sec, UTC_TIMESTAMP()),
        v.update_time = UTC_TIMESTAMP()
    WHERE s.iodevice_id = _device_id
      AND s.user_id = _user_id
      AND c.is_virtual = 1
      AND v.value IS NOT NULL;
END;
