CREATE OR REPLACE FUNCTION supla_add_em_current_log_item(
    _date timestamp,
    _channel_id integer,
    _phase_no smallint,
    _min numeric(6,3),
    _max numeric(6,3),
    _avg numeric(6,3)
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_em_current_log (
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