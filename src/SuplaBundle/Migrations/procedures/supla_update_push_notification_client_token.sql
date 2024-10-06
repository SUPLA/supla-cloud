DROP PROCEDURE IF EXISTS `supla_update_push_notification_client_token`;

CREATE PROCEDURE `supla_update_push_notification_client_token`(IN `_user_id` INT, IN `_client_id` INT,
                                                               IN `_token` VARCHAR(255) CHARSET utf8mb4, IN `_platform` TINYINT,
                                                               IN `_app_id` INT, IN `_devel_env` TINYINT,
                                                               IN `_profile_name` VARCHAR(50) CHARSET utf8mb4)
    NOT DETERMINISTIC MODIFIES SQL DATA SQL SECURITY DEFINER
UPDATE supla_client
SET push_token             = _token,
    push_token_update_time = UTC_TIMESTAMP(),
    platform               = _platform,
    app_id                 = _app_id,
    devel_env              = _devel_env,
    profile_name           = _profile_name
WHERE id = _client_id
  AND user_id = _user_id;
