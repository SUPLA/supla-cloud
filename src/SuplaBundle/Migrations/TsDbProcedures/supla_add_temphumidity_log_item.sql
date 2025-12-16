CREATE OR REPLACE FUNCTION supla_add_temphumidity_log_item(
    _channel_id INTEGER,
    _temperature NUMERIC(8,4),
    _humidity NUMERIC(8,4)
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_temphumidity_log (
    channel_id,
    date,
    temperature,
    humidity
)
VALUES (
           _channel_id,
           timezone('UTC', now()),
           _temperature,
           _humidity
       );
END;
$$;