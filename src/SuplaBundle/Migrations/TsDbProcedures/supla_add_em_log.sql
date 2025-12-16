CREATE OR REPLACE FUNCTION supla_add_em_log_item(
    _channel_id INT,
    _phase1_fae BIGINT,
    _phase1_rae BIGINT,
    _phase1_fre BIGINT,
    _phase1_rre BIGINT,
    _phase2_fae BIGINT,
    _phase2_rae BIGINT,
    _phase2_fre BIGINT,
    _phase2_rre BIGINT,
    _phase3_fae BIGINT,
    _phase3_rae BIGINT,
    _phase3_fre BIGINT,
    _phase3_rre BIGINT,
    _fae_balanced BIGINT,
    _rae_balanced BIGINT
)
RETURNS void
LANGUAGE plpgsql
SET search_path = public
SECURITY DEFINER
AS $$
BEGIN
INSERT INTO supla_em_log (
    channel_id,
    date,
    phase1_fae,
    phase1_rae,
    phase1_fre,
    phase1_rre,
    phase2_fae,
    phase2_rae,
    phase2_fre,
    phase2_rre,
    phase3_fae,
    phase3_rae,
    phase3_fre,
    phase3_rre,
    fae_balanced,
    rae_balanced
)
VALUES (
           _channel_id,
           now() AT TIME ZONE 'UTC',
           _phase1_fae,
           _phase1_rae,
           _phase1_fre,
           _phase1_rre,
           _phase2_fae,
           _phase2_rae,
           _phase2_fre,
           _phase2_rre,
           _phase3_fae,
           _phase3_rae,
           _phase3_fre,
           _phase3_rre,
           _fae_balanced,
           _rae_balanced
       );
END;
$$;