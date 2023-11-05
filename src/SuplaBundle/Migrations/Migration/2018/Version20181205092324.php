<?php

namespace SuplaBundle\Migrations\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * enabled replaced with access_token
 */
class Version20181205092324 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_google_home ADD access_token VARCHAR(255) DEFAULT NULL, DROP enabled');
    }
}
