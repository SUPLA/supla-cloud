INSERT INTO supla_user
(id, short_unique_id, long_unique_id, salt, email, password, enabled, reg_date, token, password_requested_at, limit_aid, limit_loc,
 limit_iodev, limit_client, timezone, limit_schedule, legacy_password, iodevice_reg_enabled, client_reg_enabled, limit_channel_group,
 limit_channel_per_group, rules_agreement, cookies_agreement, oauth_compat_username, oauth_compat_password, limit_direct_link,
 limit_oauth_client, locale, account_removal_requested_at, limit_scene, api_rate_limit, mqtt_broker_enabled, mqtt_broker_auth_password,
 limit_actions_per_schedule, preferences, limit_operations_per_scene, home_latitude, home_longitude)
VALUES (1, '562a0121f0a8cced85ef4574afec72ef',
        '9fc03bfb39c58c5036633415e47472cceaaf982117c4c2c450061ac06f21dcabd0f6c890c7dd0f0e8f270fe52764f7b04bda7ed0926c7da9ce148c5ab562bef1ccd97a3f98022c02066c1d07760e7acd426f9d6ccd9f5eed58cfca6fb6f5ccc9f1bddd07',
        'aq4qd2kmqowkg0cocs08o8kksg8c8gg', 'user@supla.org', '$2y$13$uEYplFmAyV8y.KM34th35.pqQ7W/gh/5yQzvAPhbuDStEF/Gqxxsa', 1,
        '2023-01-12 05:57:05.000', NULL, NULL, 10, 10, 100, 200, 'Europe/Berlin', 20, NULL, '2023-01-19 05:57:06.000',
        '2023-01-19 05:57:06.000', 20, 10, 1, 1, NULL, NULL, 50, 20, 'pl', NULL, 50, NULL, 0, NULL, 20, '{}', 20, 52.5, 13.36666);

INSERT INTO supla_location
    (id, user_id, password, caption, enabled)
VALUES (1, 1, '3c4d', 'Location #1', 1);

INSERT INTO supla_accessid
    (id, user_id, password, caption, enabled)
VALUES (1, 1, '3c4d', 'AID #1', 1);
