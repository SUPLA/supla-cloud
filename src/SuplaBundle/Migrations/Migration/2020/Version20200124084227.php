<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Introduce custom type for IP address.
 */
class Version20200124084227 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_iodevice CHANGE reg_ipv4 reg_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\', CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\'');
        $this->addSql('ALTER TABLE supla_direct_link CHANGE last_ipv4 last_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\'');
        $this->addSql('ALTER TABLE supla_audit CHANGE ipv4 ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\'');
        $this->addSql('ALTER TABLE supla_client CHANGE reg_ipv4 reg_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\', CHANGE last_access_ipv4 last_access_ipv4 INT UNSIGNED DEFAULT NULL COMMENT \'(DC2Type:ipaddress)\'');
    }
}
