DROP VIEW IF EXISTS `supla_v_client_channel`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `supla_v_client_channel` AS
select `c`.`id`                         AS `id`,
       `c`.`type`                       AS `type`,
       `c`.`func`                       AS `func`,
       ifnull(`c`.`param1`, 0)          AS `param1`,
       ifnull(`c`.`param2`, 0)          AS `param2`,
       `c`.`caption`                    AS `caption`,
       ifnull(`c`.`param3`, 0)          AS `param3`,
       ifnull(`c`.`param4`, 0)          AS `param4`,
       `c`.`text_param1`                AS `text_param1`,
       `c`.`text_param2`                AS `text_param2`,
       `c`.`text_param3`                AS `text_param3`,
       ifnull(`d`.`manufacturer_id`, 0) AS `manufacturer_id`,
       ifnull(`d`.`product_id`, 0)      AS `product_id`,
       ifnull(`c`.`user_icon_id`, 0)    AS `user_icon_id`,
       `c`.`user_id`                    AS `user_id`,
       `c`.`channel_number`             AS `channel_number`,
       `c`.`iodevice_id`                AS `iodevice_id`,
       `cl`.`id`                        AS `client_id`,
       case ifnull(`c`.`location_id`, 0)
           when 0 then `d`.`location_id`
           else `c`.`location_id` end   AS `location_id`,
       ifnull(`c`.`alt_icon`, 0)        AS `alt_icon`,
       `d`.`protocol_version`           AS `protocol_version`,
       ifnull(`c`.`flags`, 0)           AS `flags`,
       `v`.`value`                      AS `value`,
       CASE
           WHEN `v`.`valid_to` >= utc_timestamp() THEN time_to_sec(timediff(`v`.`valid_to`, utc_timestamp()))
           ELSE NULL END                AS `validity_time_sec`,
       `c`.`user_config`                AS `user_config`,
       `c`.`properties`                 AS `properties`
from (((((((`supla_dev_channel` `c` join `supla_iodevice` `d` on (`d`.`id` = `c`.`iodevice_id`)) join `supla_location` `l` on (`l`.`id` =
                                                                                                                               case ifnull(`c`.`location_id`, 0)
                                                                                                                                   when 0
                                                                                                                                       then `d`.`location_id`
                                                                                                                                   else `c`.`location_id` end)) join `supla_rel_aidloc` `r`
          on (`r`.`location_id` = `l`.`id`)) join `supla_accessid` `a` on (`a`.`id` = `r`.`access_id`)) join `supla_client` `cl`
        on (`cl`.`access_id` = `r`.`access_id`)) left join `supla_dev_channel_value` `v` on (`c`.`id` = `v`.`channel_id`)))
where (`c`.`func` is not null and `c`.`func` <> 0 or `c`.`type` = 8000)
  and ifnull(`c`.`hidden`, 0) = 0
  and `d`.`enabled` = 1
  and `l`.`enabled` = 1
  and `a`.`enabled` = 1
