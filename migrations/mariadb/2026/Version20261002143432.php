<?php
// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later

namespace SuplaBundle\Migrations\Migration;

use App\Migrations\NoWayBackMigration;

/**
 * Add the OCPP charging station configuration table.
 */
class Version20261002143432 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('CREATE TABLE supla_ocpp_charging_station (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, iodevice_id INT NOT NULL, charge_point_identity VARCHAR(64) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, auth_key_hash CHAR(128) CHARACTER SET ascii NOT NULL COLLATE `ascii_bin`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:utcdatetime)\', INDEX IDX_OCPP_CHARGING_STATION_USER (user_id), UNIQUE INDEX UNIQ_OCPP_CHARGING_STATION_DEVICE (iodevice_id), UNIQUE INDEX UNIQ_OCPP_CHARGE_POINT_IDENTITY (charge_point_identity), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE supla_ocpp_charging_station ADD CONSTRAINT FK_OCPP_CHARGING_STATION_USER FOREIGN KEY (user_id) REFERENCES supla_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE supla_ocpp_charging_station ADD CONSTRAINT FK_OCPP_CHARGING_STATION_DEVICE FOREIGN KEY (iodevice_id) REFERENCES supla_iodevice (id) ON DELETE CASCADE');
    }
}
