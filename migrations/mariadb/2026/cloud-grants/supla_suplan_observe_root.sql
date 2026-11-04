CREATE OR REPLACE PROCEDURE supla_suplan_observe_root(IN p_user INT,IN p_id BIGINT UNSIGNED)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 UPDATE supla_suplan_peer_association a LEFT JOIN supla_suplan_device_state s ON s.device_id=a.source_device_id AND s.user_id=a.user_id
  SET a.source_empty_acked=1 WHERE a.user_id=p_user AND a.id=p_id AND a.lifecycle=2 AND a.source_empty_acked=0
  AND (a.source_removed=1 OR (a.provisioned_root_epoch<>0 AND s.current_root_epoch<>a.provisioned_root_epoch));
  DELETE FROM supla_suplan_peer_association WHERE user_id=p_user AND id=p_id
   AND source_empty_acked=1 AND destination_empty_acked=1 AND (source_removed=1 OR destination_removed=1);
  UPDATE supla_suplan_peer_association SET lifecycle=3 WHERE user_id=p_user AND id=p_id
   AND source_empty_acked=1 AND destination_empty_acked=1;
END
