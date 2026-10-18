CREATE TABLE supla_resource_access_grant (
 user_id INT NOT NULL, destination_device_id INT NOT NULL, source_device_id INT NOT NULL,
 binding_target_channel_id INT DEFAULT NULL,
 resource_type TINYINT UNSIGNED NOT NULL DEFAULT 1, resource_id INT NOT NULL,
 origin_type SMALLINT UNSIGNED NOT NULL, origin_id BIGINT UNSIGNED NOT NULL,
 permissions TINYINT UNSIGNED NOT NULL, source_type INT NOT NULL, source_function INT NOT NULL,
 peer_eligible TINYINT UNSIGNED NOT NULL, requires_peer TINYINT UNSIGNED NOT NULL DEFAULT 0,
 PRIMARY KEY(user_id,destination_device_id,resource_type,resource_id,origin_type,origin_id),
 KEY resource_grant_origin(user_id,origin_type,origin_id),
 KEY resource_grant_source(user_id,source_device_id,destination_device_id),
 KEY resource_grant_channel(resource_id),
 CONSTRAINT logical_grant_binding FOREIGN KEY(binding_target_channel_id) REFERENCES supla_dev_channel(id) ON DELETE CASCADE,
 CONSTRAINT logical_grant_user FOREIGN KEY(user_id) REFERENCES supla_user(id) ON DELETE CASCADE,
 CONSTRAINT logical_grant_destination FOREIGN KEY(destination_device_id) REFERENCES supla_iodevice(id) ON DELETE CASCADE,
 CONSTRAINT logical_grant_source FOREIGN KEY(source_device_id) REFERENCES supla_iodevice(id) ON DELETE CASCADE,
 CONSTRAINT logical_grant_channel FOREIGN KEY(resource_id) REFERENCES supla_dev_channel(id) ON DELETE CASCADE,
 CONSTRAINT logical_grant_binding_identity CHECK(
  (origin_type=2 AND binding_target_channel_id IS NULL)
  OR (origin_type=1 AND (origin_id & 255) IN (5,6) AND binding_target_channel_id IS NOT NULL AND resource_id=(origin_id >> 8))
  OR (origin_type=1 AND (origin_id & 255) NOT IN (5,6) AND binding_target_channel_id IS NULL)),
 CONSTRAINT logical_grant_valid CHECK(resource_type=1 AND origin_type IN (1,2) AND origin_id>0
   AND permissions BETWEEN 1 AND 7 AND peer_eligible IN (0,1) AND requires_peer IN (0,1)
   AND source_device_id<>destination_device_id AND (requires_peer=0 OR peer_eligible=1))
) ENGINE=InnoDB;
CREATE TABLE supla_suplan_changed_pair (
 association_id BIGINT UNSIGNED NOT NULL, user_id INT NOT NULL,
 revision BIGINT UNSIGNED NOT NULL DEFAULT 1,
 PRIMARY KEY(association_id), KEY suplan_dirty_user(user_id,association_id),
 CONSTRAINT suplan_dirty_pair FOREIGN KEY(association_id) REFERENCES supla_suplan_peer_association(id) ON DELETE CASCADE,
 CONSTRAINT suplan_dirty_user FOREIGN KEY(user_id) REFERENCES supla_user(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE supla_resource_access_origin_lock (
 user_id INT NOT NULL, origin_type SMALLINT UNSIGNED NOT NULL, origin_id BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY(user_id,origin_type,origin_id),
 CONSTRAINT resource_origin_user FOREIGN KEY(user_id) REFERENCES supla_user(id) ON DELETE CASCADE
) ENGINE=InnoDB;
