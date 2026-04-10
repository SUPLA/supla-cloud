DROP PROCEDURE IF EXISTS supla_mark_gate_closed;

CREATE PROCEDURE `supla_mark_gate_closed`(IN `_channel_id` INT)
    MODIFIES SQL DATA
UPDATE
    `supla_auto_gate_closing`
SET seconds_open    = NULL,
    closing_attempt = NULL,
    last_seen_open  = NULL
WHERE channel_id = _channel_id;
