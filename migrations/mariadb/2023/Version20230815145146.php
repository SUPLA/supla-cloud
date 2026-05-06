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
 * Add time conditions to VBT.
 * Add home coordinates to user.
 */
class Version20230815145146 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD home_latitude NUMERIC(9, 6) DEFAULT NULL, ADD home_longitude NUMERIC(9, 6) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_value_based_trigger ADD active_from DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', ADD active_to DATETIME DEFAULT NULL COMMENT \'(DC2Type:utcdatetime)\', ADD active_hours VARCHAR(768) DEFAULT NULL, ADD activity_conditions VARCHAR(1024) DEFAULT NULL');
        $this->generateUserHomeCoordinates();
        $this->addSql('ALTER TABLE supla_user CHANGE home_latitude home_latitude NUMERIC(9, 6) NOT NULL');
        $this->addSql('ALTER TABLE supla_user CHANGE home_longitude home_longitude NUMERIC(9, 6) NOT NULL');
    }

    private function generateUserHomeCoordinates() {
        $users = $this->fetchAll('SELECT id, `timezone` FROM supla_user');
        foreach ($users as $user) {
            $timezone = new \DateTimeZone($user['timezone'] ?? null ?: date_default_timezone_get());
            $location = $timezone->getLocation();
            if (!$location) {
                $timezone = new \DateTimeZone('Europe/Warsaw');
                $location = $timezone->getLocation();
            }
            $this->addSql(
                'UPDATE supla_user SET home_latitude=:latitude, home_longitude=:longitude WHERE id=:id',
                ['id' => $user['id'], 'latitude' => $location['latitude'], 'longitude' => $location['longitude']]
            );
        }
    }
}
