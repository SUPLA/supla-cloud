<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add disable_http_get to direct links.
 */
class Version20190117075805 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_direct_link ADD disable_http_get TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
