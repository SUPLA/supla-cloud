DROP PROCEDURE IF EXISTS `supla_oauth_add_token_for_alexa_discover`;

CREATE PROCEDURE `supla_oauth_add_token_for_alexa_discover`(IN `_user_id` INT)
BEGIN
    DECLARE characters VARCHAR(62) DEFAULT 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    DECLARE i INT DEFAULT 1;
    DECLARE svr VARCHAR(168) DEFAULT '';
    DECLARE token VARCHAR(255) DEFAULT NULL;
    DECLARE client_id INT DEFAULT 0;

    SELECT t.client_id, SUBSTRING_INDEX(t.token, '.', -1)
    INTO client_id, svr
    FROM `supla_oauth_refresh_tokens` t
    WHERE t.user_id = _user_id
      AND t.client_id IN (SELECT id FROM `supla_oauth_clients` WHERE name = 'AMAZON ALEXA')
    ORDER BY expires_at DESC
    LIMIT 1;

    SELECT t.token
    INTO token
    FROM `supla_oauth_access_tokens` t
    WHERE t.client_id = client_id
      AND name = 'ALEXA DISCOVER'
      AND user_id = _user_id
      AND expires_at - UNIX_TIMESTAMP() >= 60;

    IF token IS NOT NULL THEN
        SELECT token;
    ELSE
        SET token = '';

        WHILE i <= 86
            DO
                SET token = CONCAT(token, SUBSTRING(characters, FLOOR(RAND() * 62) + 1, 1));
                SET i = i + 1;
            END WHILE;

        IF client_id > 0 THEN
            SET token = CONCAT(token, '.', svr);

            INSERT INTO `supla_oauth_access_tokens`(`client_id`, `user_id`, `token`, `expires_at`, `scope`, `name`, `access_id`,
                                                    `issuer_browser_string`)
            VALUES (client_id, _user_id, token, UNIX_TIMESTAMP() + 3600, 'channels_r iodevices_r locations_r account_r scenes_r',
                    'ALEXA DISCOVER', NULL, 'supla-server');

            SELECT token;
        END IF;
    END IF;
END
