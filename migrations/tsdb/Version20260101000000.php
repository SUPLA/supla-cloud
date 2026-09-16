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
 * Add general_purpose_text log table (TimescaleDB hypertable).
 */
class Version20260101000000 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_gp_text_log (channel_id INT NOT NULL, date TIMESTAMPTZ(0) NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(channel_id, date))');
        $this->addSql("COMMENT ON COLUMN supla_gp_text_log.date IS '(DC2Type:stringdatetime)'");
        $this->addSql("SELECT create_hypertable('supla_gp_text_log', 'date')");
    }
}
