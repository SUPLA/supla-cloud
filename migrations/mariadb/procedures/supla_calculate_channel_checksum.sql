DROP FUNCTION IF EXISTS `supla_calculate_channel_checksum`;

CREATE FUNCTION `supla_calculate_channel_checksum`(
    `caption` VARCHAR(100) CHARSET UTF8MB4,
    `func` int(11),
    `flist` int(11),
    `param1` int(11),
    `param2` int(11),
    `param3` int(11),
    `param4` int(11),
    `text_param1` varchar(255) CHARSET UTF8MB4,
    `text_param2` varchar(255) CHARSET UTF8MB4,
    `text_param3` varchar(255) CHARSET UTF8MB4,
    `alt_icon` int(11),
    `hidden` tinyint(1),
    `location_id` int(11),
    `flags` bigint(20) unsigned,
    `user_icon_id` int(11),
    `user_config` TEXT CHARSET UTF8MB4,
    `properties` TEXT CHARSET UTF8MB4,
    `sub_device_id` smallint(5) unsigned,
    `conflict_details` varchar(256) CHARSET UTF8MB4
) RETURNS CHAR(32)
    NO SQL
BEGIN
    DECLARE checksum CHAR(32) DEFAULT '';
    SELECT MD5(CONCAT_WS('|',
                         `caption`,
                         `func`,
                         `flist`,
                         `param1`,
                         `param2`,
                         `param3`,
                         `param4`,
                         `text_param1`,
                         `text_param2`,
                         `text_param3`,
                         `alt_icon`,
                         `hidden`,
                         `location_id`,
                         `flags`,
                         `user_icon_id`,
                         `user_config`,
                         `properties`,
                         `sub_device_id`,
                         `conflict_details`
               ))
    INTO checksum;
    RETURN checksum;
END;

DROP TRIGGER IF EXISTS update_checksum_on_dev_channel_insert;

CREATE TRIGGER update_checksum_on_dev_channel_insert
    BEFORE INSERT
    ON supla_dev_channel
    FOR EACH ROW
BEGIN
    SET NEW.checksum = supla_calculate_channel_checksum(
            NEW.`caption`,
            NEW.`func`,
            NEW.`flist`,
            NEW.`param1`,
            NEW.`param2`,
            NEW.`param3`,
            NEW.`param4`,
            NEW.`text_param1`,
            NEW.`text_param2`,
            NEW.`text_param3`,
            NEW.`alt_icon`,
            NEW.`hidden`,
            NEW.`location_id`,
            NEW.`flags`,
            NEW.`user_icon_id`,
            NEW.`user_config`,
            NEW.`properties`,
            NEW.`sub_device_id`,
            NEW.`conflict_details`
                       );
end;

DROP TRIGGER IF EXISTS update_checksum_on_dev_channel_update;

CREATE TRIGGER update_checksum_on_dev_channel_update
    BEFORE UPDATE
    ON supla_dev_channel
    FOR EACH ROW
BEGIN
    SET NEW.checksum = supla_calculate_channel_checksum(
            NEW.`caption`,
            NEW.`func`,
            NEW.`flist`,
            NEW.`param1`,
            NEW.`param2`,
            NEW.`param3`,
            NEW.`param4`,
            NEW.`text_param1`,
            NEW.`text_param2`,
            NEW.`text_param3`,
            NEW.`alt_icon`,
            NEW.`hidden`,
            NEW.`location_id`,
            NEW.`flags`,
            NEW.`user_icon_id`,
            NEW.`user_config`,
            NEW.`properties`,
            NEW.`sub_device_id`,
            NEW.`conflict_details`
                       );
end;


UPDATE supla_dev_channel
SET checksum = supla_calculate_channel_checksum(
        `caption`,
        `func`,
        `flist`,
        `param1`,
        `param2`,
        `param3`,
        `param4`,
        `text_param1`,
        `text_param2`,
        `text_param3`,
        `alt_icon`,
        `hidden`,
        `location_id`,
        `flags`,
        `user_icon_id`,
        `user_config`,
        `properties`,
        `sub_device_id`,
        `conflict_details`
               );
