<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * MQTT password hashed with SHA2-512.
 */
class Version20210105164727 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user CHANGE mqtt_broker_auth_password mqtt_broker_auth_password VARCHAR(128) DEFAULT NULL');
    }
}
