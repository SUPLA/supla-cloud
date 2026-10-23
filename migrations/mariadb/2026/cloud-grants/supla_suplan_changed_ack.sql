CREATE OR REPLACE PROCEDURE supla_suplan_changed_ack(IN p_user INT,IN p_id BIGINT UNSIGNED,IN p_revision BIGINT UNSIGNED)
SQL SECURITY DEFINER
BEGIN
 IF @@in_transaction=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='SupLAN requires caller transaction'; END IF;
 DELETE FROM supla_suplan_changed_pair WHERE user_id=p_user AND association_id=p_id AND revision=p_revision;
END
