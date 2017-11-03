/**
 * @api {get} /iodevices List
 * @apiDescription Get list of devices without their state.
 * @apiGroup IODevices
 * @apiVersion 2.0.0
 * @apiSuccessExample Success
 * {"iodevices":[{"id":1,"location_id":2,"enabled":true,"name":null,"comment":null,"registration":{"date":1509739424,"ip_v4":"0.106.111.95"},
 * "last_connected":{"date":1509739424,"ip_v4":"0.0.0.0"},"guid":"37313735-3931-36--","software_version":"2.28","protocol_version":2,
 * "channels":[{"id":1,"chnnel_number":0,"caption":null,"type":{"name":"TYPE_RELAY","id":2900},"function":{"name":"FNC_LIGHTSWITCH","id":140}},
 * {"id":2,"chnnel_number":1,"caption":null,"type":{"name":"TYPE_RELAY","id":2900},"function":{"name":"FNC_CONTROLLINGTHEDOORLOCK","id":90}},
 * {"id":3,"chnnel_number":2,"caption":null,"type":{"name":"TYPE_RELAY","id":2900},"function":{"name":"FNC_CONTROLLINGTHEGATE","id":20}},
 * {"id":4,"chnnel_number":3,"caption":null,"type":{"name":"TYPE_RELAY","id":2900},"function":{"name":"FNC_CONTROLLINGTHEROLLERSHUTTER","id":110}},
 * {"id":5,"chnnel_number":4,"caption":null,"type":{"name":"TYPE_THERMOMETERDS18B20","id":3000},"function":{"name":"FNC_THERMOMETER","id":40}}]}]}
 */
