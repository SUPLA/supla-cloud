DROP FUNCTION IF EXISTS supla_current_weekday_hour;

CREATE FUNCTION supla_current_weekday_hour(`user_timezone` VARCHAR(50))
    RETURNS VARCHAR(3)
    NO SQL
BEGIN
    DECLARE current_weekday INT;
    DECLARE current_hour INT;
    DECLARE current_time_in_user_timezone DATETIME;
    SELECT COALESCE(CONVERT_TZ(UTC_TIMESTAMP, 'UTC', `user_timezone`), UTC_TIMESTAMP) INTO current_time_in_user_timezone;
    SELECT (WEEKDAY(current_time_in_user_timezone) + 1) INTO current_weekday;
    SELECT HOUR(current_time_in_user_timezone) INTO current_hour;
    RETURN CONCAT(current_weekday, current_hour);
END;
