<?php

namespace SuplaBundle\Migrations\Migration;

use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * Add short_unique_id and long_unique_id to user.
 */
class Version20181129195431 extends NoWayBackMigration {
    public function migrate() {
        $this->rewindWrongMigrationIfNeeded();
        $this->addSql('ALTER TABLE supla_user ADD short_unique_id CHAR(32) DEFAULT NULL AFTER `id`');
        $this->addSql('ALTER TABLE supla_user ADD long_unique_id CHAR(200) DEFAULT NULL AFTER `short_unique_id`');
        $users = $this->fetchAll('SELECT id FROM supla_user');
        foreach ($users as $user) {
            $user['short_unique_id'] = bin2hex(random_bytes(16));
            $user['long_unique_id'] = bin2hex(random_bytes(100));
            $this->addSql('UPDATE supla_user SET short_unique_id=:short_unique_id, long_unique_id=:long_unique_id WHERE id=:id', $user);
        }
        $this->addSql('ALTER TABLE supla_user CHANGE short_unique_id short_unique_id CHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE supla_user CHANGE long_unique_id long_unique_id CHAR(200) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC69DAF5974 ON supla_user (short_unique_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC6AB4C1E2D ON supla_user (long_unique_id)');
    }

    private function rewindWrongMigrationIfNeeded() {
        $wrongMigrationSelector = ['version' => '20181129100106'];
        $migratedPreviously = $this->getConnection()
            ->fetchAllAssociative('SELECT * FROM migration_versions WHERE version=:version', $wrongMigrationSelector);
        if ($migratedPreviously) {
            $this->addSql('DELETE FROM `migration_versions` WHERE version=:version', $wrongMigrationSelector);
            $this->addSql('ALTER TABLE `supla_user` DROP COLUMN `short_unique_id`, DROP COLUMN `long_unique_id`, DROP INDEX `UNIQ_71BAEAC6AB4C1E2D`, DROP INDEX `UNIQ_71BAEAC69DAF5974`');
        }
    }
}
