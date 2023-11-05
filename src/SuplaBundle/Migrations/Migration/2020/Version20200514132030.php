<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Unique IODevice GUID per user.
 */
class Version20200514132030 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('DROP INDEX UNIQ_793D49D2B6FCFB2 ON supla_iodevice');
        $this->addSql('CREATE UNIQUE INDEX UNIQUE_USER_GUID ON supla_iodevice (user_id, guid)');
    }
}
