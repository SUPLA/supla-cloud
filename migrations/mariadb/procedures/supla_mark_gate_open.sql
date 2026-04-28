DROP PROCEDURE IF EXISTS supla_mark_gate_open;

CREATE PROCEDURE `supla_mark_gate_open`(IN `_channel_id` INT)
    MODIFIES SQL DATA
BEGIN
    -- We assume the server will mark open gates at least every minute.
    UPDATE
        `supla_auto_gate_closing`
    SET seconds_open    = NULL,
        closing_attempt = NULL,
        last_seen_open  = NULL
    WHERE channel_id = _channel_id
      AND last_seen_open IS NOT NULL
      AND TIMESTAMPDIFF(MINUTE, last_seen_open, UTC_TIMESTAMP()) >= 4;

    UPDATE
        `supla_auto_gate_closing`
    SET seconds_open   = IFNULL(seconds_open, 0) + IFNULL(
                UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP(last_seen_open),
                0),
        last_seen_open = UTC_TIMESTAMP()
    WHERE channel_id = _channel_id;

    SELECT max_time_open - seconds_open AS `seconds_left`
    FROM `supla_auto_gate_closing`
    WHERE channel_id = _channel_id;
END;
