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

use AppKernel;
use Doctrine\DBAL\Schema\Schema;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * supla_settings_string.
 */
class Version20230612185931 extends NoWayBackMigration {
    private const PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH = AppKernel::VAR_PATH . '/local/target-cloud-token';

    public function migrate() {
        $this->addSql('CREATE TABLE supla_settings_string (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, value VARCHAR(1024) NOT NULL, UNIQUE INDEX UNIQ_814604C95E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->moveTargetCloudTokenToSettings();
    }

    private function moveTargetCloudTokenToSettings() {
        if (file_exists(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH)) {
            $token = file_get_contents(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH);
            $this->addSql('INSERT INTO supla_settings_string (name, value) VALUES (:name, :value)', [
                'name' => InstanceSettings::TARGET_TOKEN,
                'value' => $token,
            ]);
        }
    }

    public function postUp(Schema $schema): void {
        if (file_exists(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH)) {
            unlink(self::PREVIOUS_TARGET_CLOUD_TOKEN_SAVE_PATH);
        }
    }
}
