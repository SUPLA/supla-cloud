CREATE OR REPLACE PROCEDURE supla_suplan_project(IN p_user INT, IN p_source INT, IN p_destination INT, IN p_create BOOLEAN)
SQL SECURITY DEFINER
proc: BEGIN
 DECLARE v_lock INT; DECLARE v_id BIGINT UNSIGNED DEFAULT NULL;
 DECLARE v_acl VARBINARY(534); DECLARE v_old VARBINARY(534); DECLARE v_hex TEXT;
 DECLARE v_lifecycle INT; DECLARE v_generation BIGINT UNSIGNED; DECLARE v_revision BIGINT UNSIGNED;
 DECLARE v_root BIGINT UNSIGNED DEFAULT 0; DECLARE v_provisioned BIGINT UNSIGNED; DECLARE v_count INT;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 IF v_lock IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Unknown SupLAN account'; END IF;
 SELECT id INTO v_id FROM supla_suplan_peer_association
  WHERE user_id=p_user AND source_device_id=p_source AND destination_device_id=p_destination FOR UPDATE;
 IF v_id IS NULL AND p_create AND EXISTS(
  SELECT 1 FROM supla_resource_access_grant_valid g
  JOIN supla_iodevice s ON s.id=g.source_device_id AND s.user_id=g.user_id
  JOIN supla_iodevice d ON d.id=g.destination_device_id AND d.user_id=g.user_id
  WHERE g.user_id=p_user AND g.source_device_id=p_source AND g.destination_device_id=p_destination
   AND g.peer_eligible=1 AND (s.flags & 524288)<>0 AND (d.flags & 524288)<>0
   AND s.is_virtual=0 AND d.is_virtual=0 AND s.protocol_version>=29 AND d.protocol_version>=29) THEN
  INSERT INTO supla_suplan_peer_association(user_id,source_device_id,destination_device_id,canonical_acl)
   VALUES(p_user,p_source,p_destination,X'');
  SET v_id=LAST_INSERT_ID();
 END IF;
 IF v_id IS NULL THEN LEAVE proc; END IF;
 SELECT lifecycle,peer_generation,acl_revision,canonical_acl,provisioned_root_epoch
  INTO v_lifecycle,v_generation,v_revision,v_old,v_provisioned
  FROM supla_suplan_peer_association WHERE id=v_id FOR UPDATE;
 SELECT COALESCE(MAX(current_root_epoch),0) INTO v_root FROM supla_suplan_device_state WHERE device_id=p_source AND user_id=p_user;
 SELECT COUNT(*),GROUP_CONCAT(CONCAT(LPAD(HEX(resource_type),2,'0'),LPAD(HEX(resource_id),8,'0'),
   LPAD(HEX(IF((permissions & 2)<>0,permissions & 6,permissions)),2,'0')) ORDER BY resource_type,resource_id SEPARATOR '')
 INTO v_count,v_hex FROM (
  SELECT g.resource_type,g.resource_id,BIT_OR(g.permissions) permissions FROM supla_resource_access_grant_valid g
  JOIN supla_dev_channel c ON c.id=g.resource_id AND c.user_id=g.user_id AND c.iodevice_id=g.source_device_id
   AND c.type=g.source_type AND c.func=g.source_function AND c.func<>0
  JOIN supla_iodevice s ON s.id=g.source_device_id AND s.user_id=g.user_id AND s.is_virtual=0 AND s.protocol_version>=29
  JOIN supla_iodevice d ON d.id=g.destination_device_id AND d.user_id=g.user_id AND d.is_virtual=0 AND d.protocol_version>=29
  WHERE g.user_id=p_user AND g.source_device_id=p_source AND g.destination_device_id=p_destination
   AND g.peer_eligible=1 AND (s.flags & 524288)<>0 AND (d.flags & 524288)<>0
   AND NOT EXISTS(SELECT 1 FROM supla_suplan_peer_association a WHERE a.id=v_id AND (a.source_removed=1 OR a.destination_removed=1))
  GROUP BY g.resource_type,g.resource_id FOR UPDATE
 ) rights_union;
 IF v_count>89 OR LENGTH(v_hex)<>v_count*12 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN ACL capacity exceeded'; END IF;
 SET v_acl=IFNULL(UNHEX(v_hex),X'');
 IF v_acl=v_old THEN LEAVE proc; END IF;
 IF LENGTH(v_acl)>0 AND v_lifecycle<>1 THEN
  IF v_root<>0 AND v_root=v_provisioned THEN SET v_generation=v_generation+1; END IF;
  SET v_revision=1; SET v_lifecycle=1;
 ELSEIF v_revision=4294967295 THEN SET v_generation=v_generation+1; SET v_revision=1;
 ELSE SET v_revision=v_revision+1; END IF;
 IF v_generation>4294967295 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN generation exhausted'; END IF;
 IF LENGTH(v_acl)=0 THEN SET v_lifecycle=2; END IF;
 UPDATE supla_suplan_peer_association SET lifecycle=v_lifecycle,peer_generation=v_generation,acl_revision=v_revision,
  canonical_acl=v_acl,change_revision=change_revision+1,source_empty_acked=0,destination_empty_acked=0,
  provisioned_root_epoch=IF(LENGTH(v_acl)=0 AND v_root<>0,v_root,provisioned_root_epoch) WHERE id=v_id;
 INSERT INTO supla_suplan_changed_pair(association_id,user_id,revision)
  SELECT id,user_id,change_revision FROM supla_suplan_peer_association WHERE id=v_id
  ON DUPLICATE KEY UPDATE revision=VALUES(revision);
END
