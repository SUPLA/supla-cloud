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
 * supla_energy_price_log
 */
class Version20250611080146 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_energy_price_log (date_from DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', date_to DATETIME NOT NULL COMMENT \'(DC2Type:stringdatetime)\', rce NUMERIC(8, 4) DEFAULT NULL, fixing1 NUMERIC(8, 4) DEFAULT NULL, fixing2 NUMERIC(8, 4) DEFAULT NULL, PRIMARY KEY(date_from)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
    }
}
