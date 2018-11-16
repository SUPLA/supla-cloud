<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Region field added to the supla_alexa_egc table.
 */
class Version20181116145751 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('ALTER TABLE supla_alexa_egc ADD region VARCHAR(5) DEFAULT NULL');
    }

}
