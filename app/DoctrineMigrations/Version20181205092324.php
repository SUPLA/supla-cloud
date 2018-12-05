<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * enabled replaced with access_token
 */
class Version20181205092324 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_google_home ADD access_token VARCHAR(255) DEFAULT NULL, DROP enabled');
    }
}
