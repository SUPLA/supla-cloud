DROP PROCEDURE IF EXISTS `supla_oauth_add_token_for_app`;

CREATE PROCEDURE `supla_oauth_add_token_for_app` (IN `_user_id` INT(11), IN `_token` VARCHAR(255) CHARSET utf8, IN `_expires_at` INT(11), IN `_access_id` INT(11), OUT `_id` INT(11))  NO SQL BEGIN
SET @client_id = 0;
SELECT `id` INTO @client_id FROM `supla_oauth_clients` WHERE `type` = 2 LIMIT 1;
IF @client_id <> 0 AND EXISTS(SELECT 1 FROM `supla_accessid` WHERE `user_id` = _user_id AND `id` = _access_id) THEN
  INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `access_id`) VALUES
   (@client_id, _user_id, _token, _expires_at, 'channels_r channels_files', _access_id);
SELECT LAST_INSERT_ID() INTO _id;
END IF;
END;