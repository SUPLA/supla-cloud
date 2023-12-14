INSERT INTO supla_iodevice (id, location_id, user_id, guid, name, enabled, comment, reg_date, reg_ipv4, last_connected, last_ipv4,
                            software_version, protocol_version, original_location_id, auth_key, flags, manufacturer_id, product_id,
                            user_config, properties)
VALUES (4, 1, 1, 0x39373132343039, 'LOCKED-DEVICE', 1, NULL, '2023-12-14 21:46:12.000', 1790437099, '2023-12-14 21:46:12.000', NULL, '2.16',
        2, NULL, NULL, 272, NULL, NULL, '{"statusLed": "ON_WHEN_CONNECTED"}', NULL);

INSERT INTO supla_dev_channel (iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3,
                               text_param1, text_param2, text_param3, alt_icon, hidden, location_id, flags, user_icon_id, user_config,
                               param4, properties)
VALUES (4, 1, 0, 'Consectetur molestiae quam provident.', 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (4, 1, 1, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL);
