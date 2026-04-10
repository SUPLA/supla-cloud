CREATE
OR REPLACE VIEW supla_v_accessid_active AS
SELECT sa.*,
       supla_is_now_active(
               active_from,
               active_to,
               active_hours,
               timezone
       ) is_now_active
FROM supla_accessid sa
         INNER JOIN supla_user su ON
    su.id = sa.user_id;
