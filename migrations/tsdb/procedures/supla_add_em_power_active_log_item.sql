CREATE OR REPLACE FUNCTION supla_add_em_power_active_log_item(
    _date TIMESTAMPTZ,
    _channel_id integer,
    _phase_no smallint,
    _min numeric(11,5),
    _max numeric(11,5),
    _avg numeric(11,5)
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_em_power_active_log (
    "date",
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