CREATE OR REPLACE PROCEDURE supla_resource_access_pending(IN p_user INT,IN p_destination INT,IN p_channel INT,
 IN p_permissions INT,IN p_mode INT,IN p_allow BOOLEAN)
SQL SECURITY DEFINER
proc: BEGIN
 DECLARE v_lock INT; DECLARE v_pair INT; DECLARE v_source INT; DECLARE v_missing INT;
 DECLARE v_decision INT DEFAULT NULL; DECLARE v_status VARCHAR(8); DECLARE v_permissions INT; DECLARE v_pending INT;
 IF @@in_transaction=0 OR p_permissions NOT BETWEEN 1 AND 7 OR p_mode NOT IN (1,2) OR (p_mode=2 AND p_permissions<>1) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid SupLAN demand'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT c.iodevice_id INTO v_source FROM supla_dev_channel c JOIN supla_iodevice d ON d.id=p_destination AND d.user_id=p_user
  JOIN supla_suplan_device_state i ON i.device_id=d.id AND i.user_id=p_user
  WHERE c.id=p_channel AND c.user_id=p_user AND c.func<>0 AND c.iodevice_id<>d.id
   AND d.is_virtual=0 AND d.protocol_version>=29 AND (d.flags & 524288)<>0;
 IF v_source IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid SupLAN demand owner'; END IF;
 IF p_mode=1 AND NOT EXISTS(SELECT 1 FROM supla_iodevice s JOIN supla_suplan_device_state i ON i.device_id=s.id AND i.user_id=s.user_id
  WHERE s.id=v_source AND s.user_id=p_user AND s.is_virtual=0 AND s.protocol_version>=29 AND (s.flags & 524288)<>0) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Source does not support SupLAN peer'; END IF;
 -- This procedure is called ONLY for an actual ENSURE, never value publication/quiet checks.
 -- Recording existing consent's requested delivery is independent of permission to create Pending.
 IF p_mode=2 THEN
  UPDATE supla_resource_access_decision d JOIN supla_resource_access_pair p ON p.id=d.pair_id
   SET d.requested_delivery_mode=2
   WHERE p.user_id=p_user AND p.destination_device_id=p_destination AND p.source_channel_id=p_channel
    AND p.source_device_id=v_source AND d.status='ACCEPTED' AND (d.permissions & 3)<>0
    AND d.requested_delivery_mode<>2;
 END IF;
 IF p_allow IS NULL OR p_allow<>1 THEN LEAVE proc; END IF;
 SELECT COALESCE(BIT_OR(permissions),0) INTO v_permissions FROM supla_resource_access_grant_valid
  WHERE user_id=p_user AND destination_device_id=p_destination AND resource_type=1 AND resource_id=p_channel;
 SET v_missing=(IF((p_permissions & 2)<>0,p_permissions & 6,p_permissions) & ~v_permissions);
 IF v_missing=0 THEN LEAVE proc; END IF;
 INSERT INTO supla_resource_access_pair(user_id,destination_device_id,source_device_id,source_channel_id)
  VALUES(p_user,p_destination,v_source,p_channel) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id);
 SET v_pair=LAST_INSERT_ID();
 SELECT id,status,permissions INTO v_decision,v_status,v_permissions FROM supla_resource_access_decision
  WHERE open_pair_id=v_pair FOR UPDATE;
 IF v_status='REJECTED' THEN LEAVE proc; END IF;
 IF v_decision IS NULL THEN
  INSERT INTO supla_resource_access_decision(pair_id,open_pair_id,status,permissions,requested_delivery_mode)
   VALUES(v_pair,v_pair,'PENDING',v_missing,p_mode);
 ELSE
  SET v_pending=(v_permissions | v_missing);
  SET v_pending=IF((v_pending & 2)<>0,v_pending & 6,v_pending);
  UPDATE supla_resource_access_decision SET permissions=v_pending,
   requested_delivery_mode=IF(v_pending=1,p_mode,1) WHERE id=v_decision;
 END IF;
END
