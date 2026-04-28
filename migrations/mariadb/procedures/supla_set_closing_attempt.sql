DROP PROCEDURE IF EXISTS supla_set_closing_attempt;

CREATE PROCEDURE `supla_set_closing_attempt`(IN `_channel_id` INT)
    MODIFIES SQL DATA
UPDATE
    `supla_auto_gate_closing`
SET closing_attempt = UTC_TIMESTAMP()
WHERE channel_id = _channel_id;
