CREATE OR REPLACE PROCEDURE supla_suplan_peer_demand(IN p_user INT,IN p_destination INT,IN p_channel INT)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_source INT DEFAULT NULL; DECLARE v_lock INT; DECLARE v_channels VARBINARY(512); DECLARE v_offset INT DEFAULT 1; DECLARE v_found BOOLEAN DEFAULT FALSE;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT g.source_device_id,i.accepted_channels INTO v_source,v_channels FROM supla_resource_access_grant g
  JOIN supla_suplan_device_state i ON i.device_id=g.source_device_id AND i.user_id=g.user_id
  JOIN supla_suplan_device_state d ON d.device_id=g.destination_device_id AND d.user_id=g.user_id
  WHERE g.user_id=p_user AND g.destination_device_id=p_destination AND g.resource_type=1 AND g.resource_id=p_channel
   AND g.peer_eligible=1 LIMIT 1;
 WHILE v_offset<=LENGTH(v_channels) DO
  IF CONV(HEX(SUBSTRING(v_channels,v_offset,4)),16,10)=p_channel THEN SET v_found=TRUE; END IF;
  SET v_offset=v_offset+4;
 END WHILE;
 IF v_source IS NULL OR v_found=FALSE THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No committed SupLAN authority'; END IF;
 CALL supla_suplan_project(p_user,v_source,p_destination,TRUE);
END
