CREATE OR REPLACE FUNCTION supla_add_ic_log_item(
    _channel_id INTEGER,
    _counter BIGINT,
    _calculated_value BIGINT
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_ic_log (
    channel_id,
    date,
    counter,
    calculated_value
)
VALUES (
           _channel_id,
           timezone('UTC', now()),
           _counter,
           _calculated_value
       );
END;
$$;