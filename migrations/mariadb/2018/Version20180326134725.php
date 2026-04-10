<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add rules & cookies agreements to user.
 */
class Version20180326134725 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD rules_agreement TINYINT(1) DEFAULT \'0\' NOT NULL, ADD cookies_agreement TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
