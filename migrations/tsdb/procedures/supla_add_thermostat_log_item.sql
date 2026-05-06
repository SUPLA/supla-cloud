CREATE OR REPLACE FUNCTION supla_add_thermostat_log_item(
    _channel_id integer,
    _measured_temperature numeric(5,2),
    _preset_temperature numeric(5,2),
    _on BOOLEAN
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_thermostat_log (
    channel_id,
    "date",
    measured_temperature,
    preset_temperature,
    "on"
)
VALUES (
           _channel_id,
           now() AT TIME ZONE 'UTC',
           _measured_temperature,
           _preset_temperature,
           _on
       );
END;
$$;