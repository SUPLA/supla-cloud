<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * EndpointScope field added to the supla_alexa_egc table.
 */
class Version20181117124914 extends NoWayBackMigration
{
    public function migrate() {
        $this->addSql('ALTER TABLE supla_alexa_egc ADD endpoint_scope VARCHAR(16) NOT NULL');
    }

}
