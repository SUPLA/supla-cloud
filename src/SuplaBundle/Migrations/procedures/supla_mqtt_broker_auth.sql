DROP PROCEDURE IF EXISTS `supla_mqtt_broker_auth`;

CREATE PROCEDURE `supla_mqtt_broker_auth`(IN `in_suid` VARCHAR(255) CHARSET utf8mb4, IN `in_password` VARCHAR(255) CHARSET utf8mb4)
    NOT DETERMINISTIC READS SQL DATA SQL SECURITY DEFINER
BEGIN
    SET @hashed_password = SHA2(in_password, 512);
    SELECT 1
    FROM supla_user su
             LEFT JOIN supla_oauth_client_authorizations soca ON su.id = soca.user_id
    WHERE mqtt_broker_enabled = 1
      AND short_unique_id = BINARY in_suid
      AND (su.mqtt_broker_auth_password = @hashed_password OR soca.mqtt_broker_auth_password = @hashed_password)
    LIMIT 1;
END;
