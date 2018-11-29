<?php

namespace Supla\Migrations;

use Ramsey\Uuid\Uuid;

/**
 * Add uuid and random_id to user.
 */
class Version20181129100106 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_user ADD uuid CHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_user ADD random_id CHAR(200) DEFAULT NULL');
        $users = $this->fetchAll('SELECT id FROM supla_user');
        foreach ($users as $user) {
            $user['uuid'] = Uuid::uuid4();
            $user['random_id'] = bin2hex(random_bytes(100));
            $this->addSql('UPDATE supla_user SET uuid=:uuid, random_id=:random_id WHERE id=:id', $user);
        }
        $this->addSql('ALTER TABLE supla_user CHANGE uuid uuid CHAR(36) NOT NULL');
        $this->addSql('ALTER TABLE supla_user CHANGE random_id random_id CHAR(200) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC6D17F50A6 ON supla_user (uuid)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_71BAEAC627DFF64C ON supla_user (random_id)');
    }
}
