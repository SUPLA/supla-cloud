DROP FUNCTION IF EXISTS `supla_calculate_device_checksum`;

CREATE FUNCTION `supla_calculate_device_checksum`(
    `location_id` int(11),
    `name` VARCHAR(100) CHARSET UTF8MB4,
    `enabled` tinyint(1),
    `comment` VARCHAR(200) CHARSET UTF8MB4,
    `last_ipv4` int(10) unsigned,
    `software_version` VARCHAR(20) CHARSET UTF8MB4,
    `protocol_version` int(11),
    `flags` int(11),
    `user_config` varchar(4096) CHARSET UTF8MB4,
    `properties` varchar(2048) CHARSET UTF8MB4
) RETURNS CHAR(32)
    NO SQL
BEGIN
    DECLARE checksum CHAR(32) DEFAULT '';
    SELECT MD5(CONCAT_WS('|',
                         `location_id`,
                         `name`,
                         `enabled`,
                         `comment`,
                         `last_ipv4`,
                         `software_version`,
                         `protocol_version`,
                         `flags`,
                         `user_config`,
                         `properties`
               ))
    INTO checksum;
    RETURN checksum;
END;

DROP TRIGGER IF EXISTS update_checksum_on_device_insert;

CREATE TRIGGER update_checksum_on_device_insert
    BEFORE INSERT
    ON supla_iodevice
    FOR EACH ROW
BEGIN
    SET NEW.checksum = supla_calculate_device_checksum(
            NEW.`location_id`,
            NEW.`name`,
            NEW.`enabled`,
            NEW.`comment`,
            NEW.`last_ipv4`,
            NEW.`software_version`,
            NEW.`protocol_version`,
            NEW.`flags`,
            NEW.`user_config`,
            NEW.`properties`
                       );
end;

DROP TRIGGER IF EXISTS update_checksum_on_device_update;

CREATE TRIGGER update_checksum_on_device_update
    BEFORE UPDATE
    ON supla_iodevice
    FOR EACH ROW
BEGIN
    SET NEW.checksum = supla_calculate_device_checksum(
            NEW.`location_id`,
            NEW.`name`,
            NEW.`enabled`,
            NEW.`comment`,
            NEW.`last_ipv4`,
            NEW.`software_version`,
            NEW.`protocol_version`,
            NEW.`flags`,
            NEW.`user_config`,
            NEW.`properties`
                       );
end;


UPDATE supla_iodevice
SET checksum = supla_calculate_device_checksum(
        `location_id`,
        `name`,
        `enabled`,
        `comment`,
        `last_ipv4`,
        `software_version`,
        `protocol_version`,
        `flags`,
        `user_config`,
        `properties`
               );
