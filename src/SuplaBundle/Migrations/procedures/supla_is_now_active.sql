DROP FUNCTION IF EXISTS `supla_is_now_active`;

CREATE FUNCTION supla_is_now_active(
    `active_from` DATETIME,
    `active_to` DATETIME,
    `active_hours` VARCHAR(768),
    `user_timezone` VARCHAR(50)
) RETURNS INT(11)
    CONTAINS SQL
BEGIN
    DECLARE res INT DEFAULT 1;
    IF `active_from` IS NOT NULL THEN
        SELECT (active_from <= UTC_TIMESTAMP)
        INTO res;
    END IF;
    IF res = 1 AND `active_to` IS NOT NULL THEN
        SELECT (active_to >= UTC_TIMESTAMP) INTO res;
    END IF;
    IF res = 1 AND `active_hours` IS NOT NULL THEN
        SELECT (`active_hours` LIKE CONCAT('%,', supla_current_weekday_hour(`user_timezone`), ',%') COLLATE utf8mb4_unicode_ci)
        INTO res;
    END IF;
    RETURN res;
END;
