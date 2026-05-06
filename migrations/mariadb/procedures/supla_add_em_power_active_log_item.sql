DROP PROCEDURE IF EXISTS `supla_add_em_power_active_log_item`;

CREATE PROCEDURE `supla_add_em_power_active_log_item`(
    IN `_date` DATETIME,
    IN `_channel_id` INT(11),
    IN `_phase_no` TINYINT,
    IN `_min` NUMERIC(11, 5),
    IN `_max` NUMERIC(11, 5),
    IN `_avg` NUMERIC(11, 5)
)
    NOT DETERMINISTIC
    MODIFIES SQL DATA
BEGIN
    INSERT INTO `supla_em_power_active_log` (`date`, channel_id, phase_no, min, max, avg)
    VALUES (_date, _channel_id, _phase_no, _min, _max, _avg);
END;
