CREATE OR REPLACE PROCEDURE supla_suplan_peer_ack(IN p_user INT,IN p_id BIGINT UNSIGNED,IN p_root BIGINT UNSIGNED,
 IN p_generation BIGINT UNSIGNED,IN p_revision BIGINT UNSIGNED,IN p_side INT,IN p_empty BOOLEAN)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT; DECLARE v_id BIGINT UNSIGNED DEFAULT NULL; DECLARE v_lifecycle INT;
 IF @@in_transaction=0 OR p_side NOT IN (1,2) OR p_empty NOT IN (0,1) OR p_root NOT BETWEEN 1 AND 4294967294 THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid SupLAN ACK'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT a.id,a.lifecycle INTO v_id,v_lifecycle FROM supla_suplan_peer_association a
  LEFT JOIN supla_suplan_device_state s ON s.device_id=a.source_device_id AND s.user_id=a.user_id
  WHERE a.id=p_id AND a.user_id=p_user AND a.peer_generation=p_generation AND a.acl_revision=p_revision
  AND ((p_empty=1 AND LENGTH(a.canonical_acl)=0 AND a.lifecycle IN (2,3) AND a.provisioned_root_epoch=p_root)
   OR (p_empty=0 AND LENGTH(a.canonical_acl)>0 AND a.lifecycle=1 AND a.source_removed=0 AND a.destination_removed=0
    AND s.current_root_epoch=p_root AND (p_side=1 OR (a.provisioned_root_epoch=p_root AND a.provisioned_peer_generation=p_generation)))) FOR UPDATE;
 IF v_id IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Stale SupLAN ACK'; END IF;
 IF p_empty THEN
  UPDATE supla_suplan_peer_association SET source_empty_acked=IF(p_side=1,1,source_empty_acked),
   destination_empty_acked=IF(p_side=2,1,destination_empty_acked) WHERE id=v_id;
  DELETE FROM supla_suplan_peer_association WHERE user_id=p_user AND id=v_id
   AND source_empty_acked=1 AND destination_empty_acked=1 AND (source_removed=1 OR destination_removed=1);
  UPDATE supla_suplan_peer_association SET lifecycle=3 WHERE user_id=p_user AND id=v_id
   AND source_empty_acked=1 AND destination_empty_acked=1;
 ELSEIF p_side=1 THEN
  -- Commit EXACT acknowledged Source context, including the first root=0 -> accepted root.
  -- Never reset or advance generation/revision after Source has confirmed those bytes.
  UPDATE supla_suplan_peer_association SET provisioned_root_epoch=p_root,provisioned_peer_generation=p_generation WHERE id=v_id;
 END IF;
END
