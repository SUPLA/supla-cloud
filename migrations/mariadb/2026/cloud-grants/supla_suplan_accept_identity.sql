CREATE OR REPLACE PROCEDURE supla_suplan_accept_identity(IN p_user INT,IN p_device INT,IN p_root BIGINT UNSIGNED,IN p_channels VARBINARY(512))
SQL SECURITY DEFINER
BEGIN
 DECLARE v_lock INT; DECLARE v_index INT DEFAULT 1; DECLARE v_previous BIGINT UNSIGNED DEFAULT 0; DECLARE v_channel BIGINT UNSIGNED;
 IF @@in_transaction=0 OR p_root NOT BETWEEN 1 AND 4294967294 OR LENGTH(p_channels)%4<>0 OR p_channels IS NULL THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid accepted SERVER identity'; END IF;
 SELECT id INTO v_lock FROM supla_user WHERE id=p_user FOR UPDATE;
 SET v_lock=NULL;
 SELECT id INTO v_lock FROM supla_iodevice WHERE id=p_device AND user_id=p_user AND is_virtual=0
  AND protocol_version>=29 AND (flags & 524288)<>0 FOR UPDATE;
 IF v_lock IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid SERVER device'; END IF;
 WHILE v_index<=LENGTH(p_channels) DO
  SET v_channel=CONV(HEX(SUBSTRING(p_channels,v_index,4)),16,10);
  IF v_channel<=v_previous OR v_channel>2147483647 OR NOT EXISTS(
   SELECT 1 FROM supla_dev_channel WHERE id=v_channel AND user_id=p_user AND iodevice_id=p_device) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid accepted channel mapping'; END IF;
  SET v_previous=v_channel; SET v_index=v_index+4;
 END WHILE;
 INSERT INTO supla_suplan_device_state(device_id,user_id,current_root_epoch,accepted_channels)
  VALUES(p_device,p_user,p_root,p_channels) ON DUPLICATE KEY UPDATE current_root_epoch=VALUES(current_root_epoch),accepted_channels=VALUES(accepted_channels);
END
