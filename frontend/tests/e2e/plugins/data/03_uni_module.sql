INSERT INTO supla_iodevice (location_id, user_id, guid, name, enabled, comment, reg_date, reg_ipv4, last_connected, last_ipv4,
                            software_version, protocol_version, original_location_id, auth_key, flags, manufacturer_id, product_id)
VALUES (1, 1, 0x37363433313136, 'UNI-MODULE', 1, NULL, '2023-07-13 22:59:42.000', 3326686544, '2023-07-13 22:59:42.000', NULL, '2.4', 2,
        NULL, NULL, 48, NULL, NULL);

INSERT INTO supla_dev_channel (id, iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3, text_param1,
                               text_param2, text_param3, alt_icon, hidden, location_id, flags, user_icon_id, user_config, param4,
                               properties)
VALUES (4, 2, 1, 0, NULL, 2900, 140, 96, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (5, 2, 1, 1, NULL, 2900, 90, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (6, 2, 1, 2, NULL, 2900, 20, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (7, 2, 1, 3, NULL, 2900, 110, 16, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 16384, NULL, NULL, 0, NULL),
       (8, 2, 1, 4, NULL, 1000, 50, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (9, 2, 1, 5, NULL, 1010, 100, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (10, 2, 1, 6, NULL, 3000, 40, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, NULL),
       (11, 2, 1, 7, NULL, 11000, 700, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0,
        '{"actionTriggerCapabilities":["SHORT_PRESS_X3","TOGGLE_X3","TOGGLE_X2","SHORT_PRESS_X2","HOLD"]}'),
       (12, 2, 1, 8, NULL, 8000, 110, 65535, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 4096, NULL, NULL, 0, NULL),
       (13, 2, 1, 9, NULL, 5010, 330, NULL, 0, 0, 0, NULL, 'AA', NULL, 0, 0, NULL, 8192, NULL,
        '{"pricePerUnit":0,"impulsesPerUnit":0,"currency":null,"unit":"AA","initialValue":0,"addToHistory":false,"resetCountersAvailable":true,"relatedChannelId":null}',
        0, NULL);
