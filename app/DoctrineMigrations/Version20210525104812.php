<?php declare(strict_types=1);

namespace Supla\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * New schedule modes.
 */
final class Version20210525104812 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_schedule CHANGE time_expression time_expression VARCHAR(100) DEFAULT NULL, CHANGE action action INT DEFAULT NULL');
        $updateConcat = <<<UPDATE
CONCAT('[{"cron":"', time_expression, '","action":{"id":', `action`, ',"param":',COALESCE(action_param, 'null'), '}]')
UPDATE;
        $this->addSql("UPDATE supla_schedule SET config=$updateConcat WHERE mode = 'daily'");
        $this->addSql('UPDATE supla_schedule SET time_expression=NULL, action=NULL, action_param=NULL WHERE mode = "daily"');
    }
}
