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

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Fixes to procedures for enabling and disabling schedules.
 */
class Version20230529143433 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_enable_schedule`');
        $this->addSql('DROP PROCEDURE IF EXISTS `supla_disable_schedule`');
        $this->addSql('CREATE PROCEDURE `supla_enable_schedule`(IN `_user_id` INT, IN `_id` INT) UPDATE supla_schedule SET enabled = 1, next_calculation_date = UTC_TIMESTAMP() WHERE id = _id AND user_id = _user_id AND enabled != 1');
        $this->addSql('CREATE PROCEDURE `supla_disable_schedule`(IN `_user_id` INT, IN `_id` INT) BEGIN UPDATE supla_schedule SET enabled = 0 WHERE id = _id AND user_id = _user_id; DELETE FROM supla_scheduled_executions WHERE schedule_id = _id AND planned_timestamp >= UTC_TIMESTAMP(); END');
    }
}
