<?php

namespace Supla\Migrations;

use Ramsey\Uuid\Uuid;

/**
 * Add uuid and random_id to user.
 */
class Version20181129100106 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD short_unique_id CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_user ADD long_unique_id CHAR(200) DEFAULT NULL');
        $users = $this->fetchAll('SELECT id FROM supla_user');
        foreach ($users as $user) {
            $user['short_unique_id'] = Uuid::uuid4();
            $user['long_unique_id'] = bin2hex(random_bytes(100));
            $this->addSql('UPDATE supla_user SET short_unique_id=:short_unique_id, long_unique_id=:long_unique_id WHERE id=:id', $user);
        }
        $this->addSql('ALTER TABLE supla_user CHANGE short_unique_id short_unique_id CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE supla_user CHANGE long_unique_id long_unique_id CHAR(200) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC69DAF5974 ON supla_user (short_unique_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC6AB4C1E2D ON supla_user (long_unique_id)');
    }
}
