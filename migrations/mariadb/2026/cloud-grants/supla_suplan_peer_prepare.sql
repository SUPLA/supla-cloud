CREATE OR REPLACE PROCEDURE supla_suplan_peer_prepare(IN p_user INT,IN p_id BIGINT UNSIGNED,
 IN p_generation BIGINT UNSIGNED,IN p_revision BIGINT UNSIGNED,IN p_root BIGINT UNSIGNED)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT; DECLARE v_id BIGINT UNSIGNED DEFAULT NULL;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT a.id INTO v_id
  FROM supla_suplan_peer_association a JOIN supla_suplan_device_state s ON s.device_id=a.source_device_id AND s.user_id=a.user_id
  WHERE a.id=p_id AND a.user_id=p_user AND a.peer_generation=p_generation AND a.acl_revision=p_revision
   AND s.current_root_epoch=p_root AND p_root BETWEEN 1 AND 4294967294
   AND a.lifecycle=1 AND a.source_removed=0 AND a.destination_removed=0 FOR UPDATE;
 IF v_id IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Stale SupLAN preparation'; END IF;
 -- Validation/locking only. Root is recorded ONLY after Source OK, never before sending or forwarding credentials.
END
