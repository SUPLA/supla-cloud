CREATE OR REPLACE PROCEDURE supla_resource_access_replace(IN p_user INT, IN p_type INT, IN p_origin BIGINT UNSIGNED, IN p_grants LONGTEXT)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_done BOOLEAN DEFAULT FALSE; DECLARE v_source INT; DECLARE v_destination INT; DECLARE v_create BOOLEAN;
 DECLARE v_lock INT;
 DECLARE affected CURSOR FOR
  SELECT source_device_id,destination_device_id,MAX(requires_peer) FROM (
   SELECT source_device_id,destination_device_id,requires_peer FROM supla_resource_access_grant
    WHERE user_id=p_user AND origin_type=p_type AND origin_id=p_origin
   UNION ALL SELECT source_device_id,destination_device_id,requires_peer FROM JSON_TABLE(p_grants,'$[*]' COLUMNS(
    source_device_id INT PATH '$.source',destination_device_id INT PATH '$.destination',requires_peer INT PATH '$.requiresPeer')) j
  ) pairs GROUP BY source_device_id,destination_device_id ORDER BY source_device_id,destination_device_id;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done=TRUE;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Logical replacement requires transaction'; END IF;
 IF p_type NOT IN (1,2) OR p_origin=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid logical origin identity'; END IF;
 IF JSON_VALID(p_grants)=0 OR JSON_TYPE(p_grants)<>'ARRAY' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid logical grant JSON'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 IF v_lock IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Unknown SupLAN account'; END IF;
 INSERT IGNORE INTO supla_resource_access_origin_lock VALUES(p_user,p_type,p_origin);
 SELECT user_id INTO v_lock FROM supla_resource_access_origin_lock WHERE user_id=p_user AND origin_type=p_type AND origin_id=p_origin FOR UPDATE;
 IF EXISTS(SELECT 1 FROM JSON_TABLE(p_grants,'$[*]' COLUMNS(
   source_device_id INT PATH '$.source',destination_device_id INT PATH '$.destination',resource_id INT PATH '$.resource',
   permissions INT PATH '$.permissions',source_type INT PATH '$.type',source_function INT PATH '$.function',
   binding_target_channel_id INT PATH '$.bindingTarget',peer_eligible INT PATH '$.peerEligible',requires_peer INT PATH '$.requiresPeer')) j
  LEFT JOIN supla_dev_channel c ON c.id=j.resource_id AND c.user_id=p_user AND c.iodevice_id=j.source_device_id
  LEFT JOIN supla_dev_channel b ON b.id=j.binding_target_channel_id AND b.user_id=p_user AND b.iodevice_id=j.destination_device_id
  LEFT JOIN supla_iodevice s ON s.id=j.source_device_id AND s.user_id=p_user
  LEFT JOIN supla_iodevice d ON d.id=j.destination_device_id AND d.user_id=p_user
  WHERE c.id IS NULL OR s.id IS NULL OR d.id IS NULL OR c.func=0 OR c.func<>j.source_function OR c.type<>j.source_type
   OR j.permissions NOT BETWEEN 1 AND 7 OR j.permissions IS NULL OR j.source_device_id=j.destination_device_id
   OR (j.binding_target_channel_id IS NOT NULL AND (b.id IS NULL OR p_type<>1 OR (p_origin & 255) NOT IN (5,6)
    OR b.type<>2900 OR b.func<>IF((p_origin & 255)=5,960,970)))
   OR (p_type=2 AND NOT EXISTS(SELECT 1 FROM supla_resource_access_decision decision
    JOIN supla_resource_access_pair pair_identity ON pair_identity.id=decision.pair_id
    WHERE decision.id=p_origin AND decision.status='ACCEPTED' AND pair_identity.user_id=p_user
     AND pair_identity.destination_device_id=j.destination_device_id AND pair_identity.source_device_id=j.source_device_id
     AND pair_identity.source_channel_id=j.resource_id
     AND IF((decision.permissions & 2)<>0,decision.permissions | 1,decision.permissions)=IF((j.permissions & 2)<>0,j.permissions | 1,j.permissions)))
   OR (p_type=1 AND (NOT EXISTS(SELECT 1 FROM supla_dev_channel consumer WHERE consumer.id=(p_origin >> 8) AND consumer.user_id=p_user)
    OR NOT ((p_origin & 255) BETWEEN 1 AND 6 OR (p_origin & 255) BETWEEN 32 AND 41 OR (p_origin & 255) BETWEEN 64 AND 83)))
   OR j.peer_eligible IS NULL OR j.requires_peer IS NULL
   OR (j.peer_eligible=1 AND ((s.flags & 524288)=0 OR (d.flags & 524288)=0 OR s.is_virtual<>0 OR d.is_virtual<>0
    OR c.is_virtual<>0 OR s.protocol_version<29 OR d.protocol_version<29))) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid logical resource authority'; END IF;
 -- Capture the old and new affected pair set before removing this origin.
 OPEN affected;
 BEGIN
  DECLARE v_pairs LONGTEXT DEFAULT '[]'; DECLARE v_index INT DEFAULT 0;
  read_pairs: LOOP
   FETCH affected INTO v_source,v_destination,v_create;
   IF v_done THEN LEAVE read_pairs; END IF;
   SET v_pairs=JSON_ARRAY_APPEND(v_pairs,'$',JSON_OBJECT('s',v_source,'d',v_destination,'c',v_create));
  END LOOP;
  CLOSE affected;
  DELETE FROM supla_resource_access_grant WHERE user_id=p_user AND origin_type=p_type AND origin_id=p_origin;
  INSERT INTO supla_resource_access_grant(user_id,source_device_id,destination_device_id,resource_id,origin_type,origin_id,
   permissions,source_type,source_function,binding_target_channel_id,peer_eligible,requires_peer)
   SELECT p_user,source_device_id,destination_device_id,resource_id,p_type,p_origin,
    IF((permissions & 2)<>0,permissions | 1,permissions),source_type,source_function,binding_target_channel_id,peer_eligible,requires_peer
   FROM JSON_TABLE(p_grants,'$[*]' COLUMNS(source_device_id INT PATH '$.source',destination_device_id INT PATH '$.destination',
    resource_id INT PATH '$.resource',permissions INT PATH '$.permissions',source_type INT PATH '$.type',source_function INT PATH '$.function',
    binding_target_channel_id INT PATH '$.bindingTarget',peer_eligible INT PATH '$.peerEligible',requires_peer INT PATH '$.requiresPeer')) j;
  WHILE v_index<JSON_LENGTH(v_pairs) DO
   CALL supla_suplan_project(p_user,JSON_VALUE(v_pairs,CONCAT('$[',v_index,'].s')),
    JSON_VALUE(v_pairs,CONCAT('$[',v_index,'].d')),JSON_VALUE(v_pairs,CONCAT('$[',v_index,'].c')));
   SET v_index=v_index+1;
  END WHILE;
 END;
END
