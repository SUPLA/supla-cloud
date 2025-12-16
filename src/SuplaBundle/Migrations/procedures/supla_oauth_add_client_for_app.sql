DROP PROCEDURE IF EXISTS `supla_oauth_add_client_for_app`;

CREATE PROCEDURE `supla_oauth_add_client_for_app` (IN `_random_id` VARCHAR(255) CHARSET utf8, IN `_secret` VARCHAR(255) CHARSET utf8, OUT `_id` INT(11))  NO SQL BEGIN
SET @lck = 0;
SET @id_exists = 0;
SELECT GET_LOCK('oauth_add_client', 2) INTO @lck;
IF @lck = 1 THEN
SELECT id INTO @id_exists FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;
IF @id_exists <> 0 THEN
SELECT @id_exists INTO _id;
ELSE
     INSERT INTO `supla_oauth_clients`(
         `random_id`, `redirect_uris`,
         `secret`, `allowed_grant_types`, `type`) VALUES
     (_random_id, 'a:0:{}', _secret,'a:2:{i:0;s:8:"password";i:1;s:13:"refresh_token";}',2);

SELECT LAST_INSERT_ID() INTO _id;
SELECT RELEASE_LOCK('oauth_add_client') INTO @lck;
END IF;
END IF;
END;