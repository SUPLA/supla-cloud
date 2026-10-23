CREATE OR REPLACE PROCEDURE supla_suplan_cleanup(IN p_user INT,IN p_device INT)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_done BOOLEAN DEFAULT FALSE; DECLARE v_id BIGINT UNSIGNED; DECLARE v_source INT; DECLARE v_destination INT; DECLARE v_lock INT;
 DECLARE pairs CURSOR FOR SELECT id,source_device_id,destination_device_id FROM supla_suplan_peer_association
  WHERE user_id=p_user AND (source_device_id=p_device OR destination_device_id=p_device) ORDER BY source_device_id,destination_device_id;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done=TRUE;
 IF @@in_transaction=0 OR EXISTS(SELECT 1 FROM supla_iodevice WHERE id=p_device AND user_id=p_user) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cleanup requires deleted or foreign device'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 OPEN pairs;
 pair_loop: LOOP
  FETCH pairs INTO v_id,v_source,v_destination;
  IF v_done THEN LEAVE pair_loop; END IF;
  UPDATE supla_suplan_peer_association SET source_removed=IF(v_source=p_device,1,source_removed),
   destination_removed=IF(v_destination=p_device,1,destination_removed) WHERE id=v_id;
  CALL supla_suplan_project(p_user,v_source,v_destination,FALSE);
  -- A deleted endpoint cannot acknowledge. Never wait for its former connection.
  UPDATE supla_suplan_peer_association SET source_empty_acked=IF(v_source=p_device,1,source_empty_acked),
   destination_empty_acked=IF(v_destination=p_device,1,destination_empty_acked) WHERE id=v_id;
  -- No recorded Source context means no Destination key could ever have been forwarded.
  UPDATE supla_suplan_peer_association SET destination_empty_acked=1
   WHERE id=v_id AND source_removed=1 AND provisioned_root_epoch=0 AND provisioned_peer_generation=0;
  DELETE FROM supla_suplan_peer_association WHERE id=v_id AND source_empty_acked=1 AND destination_empty_acked=1;
 END LOOP;
 CLOSE pairs;
 DELETE FROM supla_suplan_device_state WHERE device_id=p_device AND user_id=p_user;
END
