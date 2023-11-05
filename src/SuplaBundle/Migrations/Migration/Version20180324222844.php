<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Channel groups limit.
 */
class Version20180324222844 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD limit_channel_group INT DEFAULT 20 NOT NULL, ADD limit_channel_per_group INT DEFAULT 10 NOT NULL');
    }
}
