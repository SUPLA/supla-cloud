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
CONCAT('[{"crontab":"', time_expression, '","action":{"id":', `action`, ',"param":',COALESCE(action_param, 'null'), '}}]')
UPDATE;
        $this->addSql("UPDATE supla_schedule SET config=$updateConcat WHERE mode = 'daily'");
        $this->addSql('UPDATE supla_schedule SET time_expression=NULL, action=NULL, action_param=NULL WHERE mode = "daily"');
        $hourly = $this->getConnection()->iterateAssociativeIndexed('SELECT id, time_expression, action, action_param FROM supla_schedule WHERE mode = "hourly"');
        foreach ($hourly as $id => $hourlySchedule) {
            $timeExpression = $hourlySchedule['time_expression'];
            $parts = explode(' ', $timeExpression);
            $config = [];
            $hours = explode(',', $parts[1]);
            foreach ($hours as $hour) {
                $parts[1] = $hour;
                $config[] = [
                    'crontab' => implode(' ', $parts),
                    'action' => [
                        'id' => intval($hourlySchedule['action']),
                        'param' => json_decode($hourlySchedule['action_param']),
                    ],
                ];
            }
            $this->addSql(
                'UPDATE supla_schedule SET mode="daily", time_expression=NULL, action=NULL, action_param=NULL, config=:config WHERE id=:id',
                ['id' => $id, 'config' => json_encode($config)]
            );
        }
    }
}
