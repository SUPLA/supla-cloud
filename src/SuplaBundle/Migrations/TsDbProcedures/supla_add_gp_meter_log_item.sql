CREATE OR REPLACE FUNCTION supla_add_gp_meter_log_item(
    _channel_id integer,
    _value double precision
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_gp_meter_log (
    channel_id,
    "date",
    value
)
VALUES (
           _channel_id,
           now() AT TIME ZONE 'UTC',
           _value
       );
END;
$$;