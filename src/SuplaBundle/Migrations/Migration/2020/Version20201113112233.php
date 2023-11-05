<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * MQTT Broker support.
 */
class Version20201113112233 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD mqtt_broker_enabled TINYINT(1) DEFAULT \'0\' NOT NULL, ADD mqtt_broker_auth_password VARCHAR(64) DEFAULT NULL');
    }
}
