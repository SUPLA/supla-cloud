CREATE OR REPLACE FUNCTION supla_add_em_voltage_aberration_log_item(
    _date                   TIMESTAMP,
    _channel_id             INTEGER,
    _phase_no               SMALLINT,
    _count_total            INTEGER,
    _count_above            INTEGER,
    _count_below            INTEGER,
    _sec_above              INTEGER,
    _sec_below              INTEGER,
    _max_sec_above          INTEGER,
    _max_sec_below          INTEGER,
    _min_voltage            NUMERIC(7,2),
    _max_voltage            NUMERIC(7,2),
    _avg_voltage            NUMERIC(7,2),
    _measurement_time_sec   INTEGER
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_em_voltage_aberration_log (
    "date",
    channel_id,
    phase_no,
    count_total,
    count_above,
    count_below,
    sec_above,
    sec_below,
    max_sec_above,
    max_sec_below,
    min_voltage,
    max_voltage,
    avg_voltage,
    measurement_time_sec
)
VALUES (
           _date,
           _channel_id,
           _phase_no,
           _count_total,
           _count_above,
           _count_below,
           _sec_above,
           _sec_below,
           _max_sec_above,
           _max_sec_below,
           _min_voltage,
           _max_voltage,
           _avg_voltage,
           _measurement_time_sec
       );
END;
$$;