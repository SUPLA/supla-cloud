DROP PROCEDURE IF EXISTS `supla_update_client`;

CREATE PROCEDURE `supla_update_client`(IN `_access_id` INT(11), IN `_name` VARCHAR(100) CHARSET utf8mb4,
                                       IN `_last_ipv4` INT(10) UNSIGNED, IN `_software_version` VARCHAR(20) CHARSET utf8,
                                       IN `_protocol_version` INT(11), IN `_auth_key` VARCHAR(64) CHARSET utf8, IN `_id` INT(11))
    MODIFIES SQL DATA
BEGIN

    UPDATE `supla_client`

    SET `access_id`        = _access_id,
        `name`             = _name,
        `last_access_date` = UTC_TIMESTAMP(),
        `last_access_ipv4` = _last_ipv4,
        `software_version` = _software_version,
        `protocol_version` = _protocol_version
    WHERE `id` = _id;

    IF
        _auth_key IS NOT NULL THEN
        UPDATE `supla_client`
        SET `auth_key` = _auth_key
        WHERE `id` = _id
          AND `auth_key` IS NULL;
    END IF;

END;
