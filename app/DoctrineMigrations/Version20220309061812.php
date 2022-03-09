<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Supla\Migrations;

/**
 * New functions: supla_current_weekday_hour, supla_is_access_id_now_active.
 * New view: supla_v_accessid_active.
 */
class Version20220309061812 extends NoWayBackMigration {
    public function migrate() {
        $this->createWeekdayHourFunction();
        $this->createIsAccessIdNowActiveFunction();
        $this->createSuplaAccessIdActiveView();
    }

    private function createWeekdayHourFunction() {
        $this->addSql('DROP FUNCTION IF EXISTS supla_current_weekday_hour');
        $function = <<<FNC
        CREATE FUNCTION supla_current_weekday_hour(`user_timezone` VARCHAR(50))
        RETURNS VARCHAR(3)
        BEGIN
            DECLARE current_weekday INT;
            DECLARE current_hour INT;
            DECLARE current_time_in_user_timezone DATETIME;
            SELECT CONVERT_TZ(CURRENT_TIMESTAMP, 'UTC', `user_timezone`) INTO current_time_in_user_timezone; 
            SELECT (WEEKDAY(current_time_in_user_timezone) + 1) INTO current_weekday;
            SELECT HOUR(current_time_in_user_timezone) INTO current_hour;
            RETURN CONCAT(current_weekday, current_hour);
        END
FNC;
        $this->addSql($function);
    }

    private function createIsAccessIdNowActiveFunction() {
        $this->addSql('DROP FUNCTION IF EXISTS supla_is_access_id_now_active');
        $function = <<<FNC
        CREATE FUNCTION supla_is_access_id_now_active(`active_from` DATETIME, `active_to` DATETIME, `active_hours` VARCHAR(768), `user_timezone` VARCHAR(50))
        RETURNS int(11)
        BEGIN
            DECLARE res INT DEFAULT 1;

            IF `active_from` IS NOT NULL THEN
              SELECT (active_from <= UTC_TIMESTAMP) INTO res;
            END IF;
            
            IF res = 1 AND `active_to` IS NOT NULL THEN
              SELECT (active_to >= UTC_TIMESTAMP) INTO res;
            END IF;

            IF res = 1 AND `active_hours` IS NOT NULL THEN
              SELECT (`active_hours` LIKE CONCAT('%,', supla_current_weekday_hour(`user_timezone`) ,',%') COLLATE utf8mb4_unicode_ci) INTO res;
            END IF;
        
            RETURN res;
        END
FNC;
        $this->addSql($function);
    }

    private function createSuplaAccessIdActiveView() {
        $view = <<<VIEW
        CREATE OR REPLACE VIEW supla_v_accessid_active AS 
        SELECT sa.*, supla_is_access_id_now_active(active_from, active_to, active_hours, timezone) is_now_active
        FROM supla_accessid sa
        INNER JOIN supla_user su ON su.id = sa.user_id;
VIEW;
        $this->addSql($view);
    }
}
