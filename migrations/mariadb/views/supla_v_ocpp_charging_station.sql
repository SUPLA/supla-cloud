-- SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
-- SPDX-License-Identifier: GPL-2.0-or-later

CREATE OR REPLACE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW supla_v_ocpp_charging_station AS
SELECT `cs`.`id`                    AS `charging_station_id`,
       `cs`.`iodevice_id`           AS `iodevice_id`,
       `cs`.`charge_point_identity` AS `charge_point_identity`,
       `cs`.`auth_key_hash`         AS `auth_key_hash`,
       `u`.`short_unique_id`        AS `short_unique_id`
FROM `supla_ocpp_charging_station` `cs`
         JOIN `supla_user` `u` ON `u`.`id` = `cs`.`user_id`
         JOIN `supla_iodevice` `d` ON `d`.`id` = `cs`.`iodevice_id` AND `d`.`user_id` = `cs`.`user_id`
WHERE `d`.`enabled` = 1;
