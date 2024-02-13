INSERT INTO supla_iodevice (id, location_id, user_id, guid, name, enabled, comment, reg_date, reg_ipv4, last_connected, last_ipv4,
                            software_version, protocol_version, original_location_id, auth_key, flags, manufacturer_id, product_id,
                            user_config, properties)
VALUES (4, 1, 1, 0x363730353133, 'Measurement Freak', 1, NULL, '2024-02-13 11:31:53', 3508131817, '2024-02-13 11:31:53', NULL, '2.21', 2,
        NULL, NULL, 0, NULL, NULL, '{"statusLed": "ON_WHEN_CONNECTED"}', NULL);


INSERT INTO supla_dev_channel (iodevice_id, user_id, channel_number, caption, `type`, func, flist, param1, param2, param3,
                               text_param1, text_param2, text_param3, alt_icon, hidden, location_id, flags, user_icon_id, user_config,
                               param4, properties)
VALUES (4, 1, 0, 'KPOP', 9000, 520, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{"valueDivider":12,"valueMultiplier":34,"valueAdded":56,"valuePrecision":2,"unitBeforeValue":"ABCD","unitAfterValue":"EFGH","noSpaceAfterValue":false,"keepHistory":true,"chartType":"CANDLE"}',
        0,
        '{"defaultValueDivider":78,"defaultValueMultiplier":910,"defaultValueAdded":1112,"defaultValuePrecision":4,"defaultUnitBeforeValue":"XCVB","defaultUnitAfterValue":"GHJK"}'),
       (4, 1, 1, 'KLOP rosnący', 9010, 530, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{"valueDivider":12,"valueMultiplier":34,"valueAdded":56,"valuePrecision":3,"unitBeforeValue":"ABCD","unitAfterValue":"EFGH","noSpaceAfterValue":false,"keepHistory":true,"chartType":"BAR","includeValueAddedInHistory":true,"fillMissingData":true,"counterType":"ALWAYS_INCREMENT"}',
        0,
        '{"defaultValueDivider":78,"defaultValueMultiplier":910,"defaultValueAdded":1112,"defaultValuePrecision":4,"defaultUnitBeforeValue":"XCVB","defaultUnitAfterValue":"GHJK"}'),
       (4, 1, 2, 'KLOP malejący', 9010, 530, NULL, 0, 0, 0, NULL, NULL, NULL, 0, 0, NULL, 0, NULL,
        '{"valueDivider":1,"valueMultiplier":1,"valueAdded":0,"valuePrecision":0,"unitBeforeValue":"","unitAfterValue":"ciastek","noSpaceAfterValue":false,"keepHistory":true,"chartType":"LINEAR","includeValueAddedInHistory":true,"fillMissingData":true,"counterType":"ALWAYS_DECREMENT"}',
        0,
        '{"defaultValueDivider":1,"defaultValueMultiplier":1,"defaultValueAdded":0,"defaultValuePrecision":0,"defaultUnitBeforeValue":"","defaultUnitAfterValue":"ciastek"}');
