CREATE OR REPLACE FUNCTION supla_add_em_voltage_log_item(
    _date TIMESTAMP,
    _channel_id INTEGER,
    _phase_no SMALLINT,
    _min NUMERIC(5,2),
    _max NUMERIC(5,2),
    _avg NUMERIC(5,2)
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_em_voltage_log (
    date,
    channel_id,
    phase_no,
    min,
    max,
    avg
)
VALUES (
           _date,
           _channel_id,
           _phase_no,
           _min,
           _max,
           _avg
       );
END;
$$;