CREATE OR REPLACE PROCEDURE supla_suplan_peer_distrust(IN p_user INT,IN p_id BIGINT UNSIGNED,
 IN p_generation BIGINT UNSIGNED,IN p_revision BIGINT UNSIGNED)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT; DECLARE v_id BIGINT UNSIGNED DEFAULT NULL;
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SELECT id INTO v_id FROM supla_suplan_peer_association WHERE user_id=p_user AND id=p_id
  AND peer_generation=p_generation AND acl_revision=p_revision AND peer_generation<4294967295 FOR UPDATE;
 IF v_id IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Stale or exhausted SupLAN distrust'; END IF;
 UPDATE supla_suplan_peer_association SET peer_generation=peer_generation+1,acl_revision=1,
  source_empty_acked=0,destination_empty_acked=0,change_revision=change_revision+1 WHERE id=v_id;
 INSERT INTO supla_suplan_changed_pair(association_id,user_id,revision)
  SELECT id,user_id,change_revision FROM supla_suplan_peer_association WHERE id=v_id ON DUPLICATE KEY UPDATE revision=VALUES(revision);
END
