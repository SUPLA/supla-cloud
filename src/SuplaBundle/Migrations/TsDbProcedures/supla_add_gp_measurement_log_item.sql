CREATE OR REPLACE FUNCTION supla_add_gp_measurement_log_item(
    _channel_id integer,
    _open_value double precision,
    _close_value double precision,
    _avg_value double precision,
    _max_value double precision,
    _min_value double precision
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_gp_measurement_log (
    channel_id,
    "date",
    open_value,
    close_value,
    avg_value,
    max_value,
    min_value
)
VALUES (
           _channel_id,
           now() AT TIME ZONE 'UTC',
           _open_value,
           _close_value,
           _avg_value,
           _max_value,
           _min_value
       );
END;
$$;