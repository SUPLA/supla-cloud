<?php

namespace Supla\Migrations;

/**
 * Introduce custom type for IP address.
 */
class Version20200124084227 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_direct_link CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\'');
    }
}
