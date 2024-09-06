CREATE OR REPLACE VIEW supla_v_auto_gate_closing AS
SELECT `c`.`user_id`         AS `user_id`,
       `c`.`enabled`         AS `enabled`,
       `dc`.`iodevice_id`    AS `device_id`,
       `c`.`channel_id`      AS `channel_id`,
       `supla_is_now_active`(
               `c`.`active_from`,
               `c`.`active_to`,
               `c`.`active_hours`,
               `u`.`timezone`
       )                     AS `is_now_active`,
       `c`.`max_time_open`   AS `max_time_open`,
       `c`.`seconds_open`    AS `seconds_open`,
       `c`.`closing_attempt` AS `closing_attempt`,
       `c`.`last_seen_open`  AS `last_seen_open`
FROM (
         `supla_auto_gate_closing` `c`
             JOIN `supla_user` `u`
             JOIN `supla_dev_channel` `dc`
         )
WHERE `c`.`user_id` = `u`.`id`
  AND `c`.`channel_id` = `dc`.`id`;
