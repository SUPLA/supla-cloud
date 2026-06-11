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

namespace SuplaBundle\Migrations\TsDbMigration;

use App\Migrations\NoWayBackMigration;

/**
 * supla_em_delta_log
 */
class Version20260611125123 extends NoWayBackMigration {
    public function migrate(): void {
        $this->addSql('CREATE TABLE supla_em_delta_log (channel_id INT NOT NULL, date TIMESTAMPTZ(0) NOT NULL, phase1_fae BIGINT DEFAULT NULL, phase1_rae BIGINT DEFAULT NULL, phase2_fae BIGINT DEFAULT NULL, phase2_rae BIGINT DEFAULT NULL, phase3_fae BIGINT DEFAULT NULL, phase3_rae BIGINT DEFAULT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql('COMMENT ON COLUMN supla_em_delta_log.date IS \'(DC2Type:stringdatetime)\'');
        $this->addSql('SELECT create_hypertable(\'supla_em_delta_log\', \'date\')');
    }
}
