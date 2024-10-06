DROP PROCEDURE IF EXISTS `supla_add_em_voltage_aberration_log_item`;

CREATE PROCEDURE `supla_add_em_voltage_aberration_log_item`(
    IN `_date` DATETIME,
    IN `_channel_id` INT(11),
    IN `_phase_no` TINYINT,
    IN `_count_total` INT(11),
    IN `_count_above` INT(11),
    IN `_count_below` INT(11),
    IN `_sec_above` INT(11),
    IN `_sec_below` INT(11),
    IN `_max_sec_above` INT(11),
    IN `_max_sec_below` INT(11),
    IN `_min_voltage` NUMERIC(7, 2),
    IN `_max_voltage` NUMERIC(7, 2),
    IN `_avg_voltage` NUMERIC(7, 2),
    IN `_measurement_time_sec` INT(11)
)
    NOT DETERMINISTIC
    MODIFIES SQL DATA
BEGIN
    INSERT INTO `supla_em_voltage_aberration_log` (`date`, channel_id, phase_no, count_total, count_above, count_below, sec_above,
                                                   sec_below,
                                                   max_sec_above, max_sec_below, min_voltage, max_voltage, avg_voltage,
                                                   measurement_time_sec)
    VALUES (_date, _channel_id, _phase_no, _count_total, _count_above, _count_below, _sec_above, _sec_below, _max_sec_above, _max_sec_below,
            _min_voltage, _max_voltage, _avg_voltage, _measurement_time_sec);

END;
