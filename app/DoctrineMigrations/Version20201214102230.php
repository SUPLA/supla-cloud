<?php

namespace Supla\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Actions in schedule executions.
 */
class Version20201214102230 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_schedule ADD config VARCHAR(1023) DEFAULT NULL');
        $this->addSql('ALTER TABLE supla_scheduled_executions ADD action INT DEFAULT NULL, ADD action_param VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE supla_scheduled_executions SET action=(SELECT action FROM schedule WHERE id=schedule_id), action_param=(SELECT action_param FROM schedule WHERE id=schedule_id)');
        $this->addSql('ALTER TABLE supla_scheduled_executions MODIFY COLUMN action action INT NOT NULL, ADD action_param VARCHAR(255) DEFAULT NULL');
    }
}
