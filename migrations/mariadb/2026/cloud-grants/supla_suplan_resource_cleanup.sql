CREATE OR REPLACE PROCEDURE supla_suplan_resource_cleanup(IN p_user INT,IN p_channel INT)
SQL SECURITY DEFINER
BEGIN
 DECLARE v_done BOOLEAN DEFAULT FALSE; DECLARE v_source INT; DECLARE v_destination INT; DECLARE v_lock INT;
 DECLARE v_pairs LONGTEXT DEFAULT '[]'; DECLARE v_index INT DEFAULT 0;
 DECLARE pairs CURSOR FOR
  WITH RECURSIVE slots(n) AS (SELECT 0 UNION ALL SELECT n+1 FROM slots WHERE n<88)
  SELECT a.source_device_id,a.destination_device_id FROM supla_suplan_peer_association a
   JOIN slots ON slots.n*6<LENGTH(a.canonical_acl)
   WHERE a.user_id=p_user AND SUBSTRING(a.canonical_acl,slots.n*6+1,5)=CONCAT(X'01',UNHEX(LPAD(HEX(p_channel),8,'0')))
  UNION
  SELECT source_device_id,destination_device_id FROM supla_resource_access_grant
   WHERE user_id=p_user AND origin_type=1 AND (origin_id >> 8)=p_channel
  ORDER BY source_device_id,destination_device_id;
 DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done=TRUE;
 IF @@in_transaction=0 OR EXISTS(SELECT 1 FROM supla_dev_channel WHERE id=p_channel AND user_id=p_user) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cleanup requires deleted or foreign channel'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 -- Canonical ACL still identifies affected peers even when resource FK deletion already removed logical rows.
 OPEN pairs;
 pair_loop: LOOP
  FETCH pairs INTO v_source,v_destination;
  IF v_done THEN LEAVE pair_loop; END IF;
  SET v_pairs=JSON_ARRAY_APPEND(v_pairs,'$',JSON_OBJECT('s',v_source,'d',v_destination));
 END LOOP;
 CLOSE pairs;
 DELETE FROM supla_resource_access_grant WHERE user_id=p_user AND origin_type=1 AND (origin_id >> 8)=p_channel;
 WHILE v_index<JSON_LENGTH(v_pairs) DO
  CALL supla_suplan_project(p_user,JSON_VALUE(v_pairs,CONCAT('$[',v_index,'].s')),JSON_VALUE(v_pairs,CONCAT('$[',v_index,'].d')),FALSE);
  SET v_index=v_index+1;
 END WHILE;
END
