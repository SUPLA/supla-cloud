INSERT INTO supla_iodevice
(id, location_id, user_id, guid, name, enabled, comment, reg_date, reg_ipv4, last_connected, last_ipv4, software_version, protocol_version,
 original_location_id, auth_key, flags, manufacturer_id, product_id, user_config, properties)
VALUES (1, 1, 1, 0x3537303031333, 'SONOFF-DS', 1, NULL, '2023-01-12 05:57:07.000', 26245388, '2023-01-12 05:57:07.000', NULL, '2.26', 2,
        NULL, NULL, 48, NULL, NULL, '{"statusLed":"ON_WHEN_CONNECTED"}', NULL);

INSERT INTO supla_dev_channel
(id, iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3, text_param1, text_param2, text_param3,
 alt_icon, hidden, location_id, flags, user_icon_id, user_config, param4, properties)
VALUES (1, 1, 1, 0, 'Et velit dolor veritatis.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL);
INSERT INTO supla_dev_channel
(id, iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3, text_param1, text_param2, text_param3,
 alt_icon, hidden, location_id, flags, user_icon_id, user_config, param4, properties)
VALUES (2, 1, 1, 1, 'Aut quaerat pariatur quo.', 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL);
INSERT INTO supla_dev_channel
(id, iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3, text_param1, text_param2, text_param3,
 alt_icon, hidden, location_id, flags, user_icon_id, user_config, param4, properties)
VALUES (3, 1, 1, 2, 'Ut quas error harum.', 11000, 700, NULL, 1, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{"actionTriggerCapabilities":["TOGGLE_X3","TOGGLE_X5","TURN_ON","TOGGLE_X4","TURN_OFF"]}');
