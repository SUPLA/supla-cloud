CREATE OR REPLACE PROCEDURE supla_suplan_share_demand(IN p_user INT,IN p_destination INT,IN p_source_channel INT,IN p_target_channel INT)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT; DECLARE v_source INT DEFAULT NULL;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT source_device_id INTO v_source FROM supla_resource_access_grant_valid
  WHERE user_id=p_user AND destination_device_id=p_destination AND resource_id=p_source_channel
   AND origin_type=1 AND (origin_id & 255) IN (5,6) AND binding_target_channel_id=p_target_channel
   AND peer_eligible=1 AND requires_peer=1 LIMIT 1;
 IF v_source IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='No exact Cloud Source-first binding'; END IF;
 CALL supla_suplan_peer_demand(p_user,p_destination,p_source_channel);
END
