CREATE OR REPLACE VIEW supla_resource_access_grant_valid AS
 SELECT g.* FROM supla_resource_access_grant g
 JOIN supla_dev_channel c ON c.id=g.resource_id AND c.user_id=g.user_id AND c.iodevice_id=g.source_device_id
  AND c.type=g.source_type AND c.func=g.source_function AND c.func<>0
 JOIN supla_iodevice s ON s.id=g.source_device_id AND s.user_id=g.user_id
 JOIN supla_iodevice d ON d.id=g.destination_device_id AND d.user_id=g.user_id
 LEFT JOIN supla_dev_channel b ON b.id=g.binding_target_channel_id AND b.user_id=g.user_id AND b.iodevice_id=g.destination_device_id
 WHERE (g.binding_target_channel_id IS NULL OR (b.type=2900 AND b.func=IF((g.origin_id & 255)=5,960,970)))
  AND d.is_virtual=0 AND d.protocol_version>=29 AND (d.flags & 524288)<>0
  AND (g.requires_peer=0 OR (s.is_virtual=0 AND c.is_virtual=0 AND s.protocol_version>=29 AND (s.flags & 524288)<>0))
  AND ((g.permissions & 4)=0 OR COALESCE(JSON_LENGTH(JSON_EXTRACT(c.properties,'$.actionTriggerCapabilities')),0)>0)
