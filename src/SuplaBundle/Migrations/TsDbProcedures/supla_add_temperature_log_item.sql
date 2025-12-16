CREATE OR REPLACE FUNCTION supla_add_temperature_log_item(
    _channel_id INTEGER,
    _temperature NUMERIC(8,4)
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_temperature_log (
    channel_id,
    date,
    temperature
)
VALUES (
           _channel_id,
           timezone('UTC', now()),
           _temperature
       );
END;
$$;
