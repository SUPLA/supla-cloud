<?php declare(strict_types=1);

namespace SuplaBundle\Migrations\Migration;

use PDO;
use SuplaBundle\Migrations\NoWayBackMigration;

/**
 * New schedule modes.
 */
final class Version20210525104812 extends NoWayBackMigration {
    public function migrate() {
        $this->addSql('ALTER TABLE supla_schedule CHANGE config config VARCHAR(2048) DEFAULT NULL');
        $updateConcat = <<<UPDATE
CONCAT('[{"crontab":"', time_expression, '","action":{"id":', `action`, ',"param":', COALESCE(action_param, 'null'), '}}]')
UPDATE;
        $this->addSql("UPDATE supla_schedule SET config=$updateConcat WHERE mode IN('daily', 'minutely', 'once')");
        $hourlyQuery = $this->getConnection()->executeQuery('SELECT id, time_expression, action, action_param FROM supla_schedule WHERE mode = "hourly"');
        while ($hourlySchedule = $hourlyQuery->fetch(PDO::FETCH_ASSOC)) {
            $id = $hourlySchedule['id'];
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
                        'param' => $hourlySchedule['action_param'] ? json_decode($hourlySchedule['action_param']) : null,
                    ],
                ];
            }
            $this->addSql(
                'UPDATE supla_schedule SET mode="daily", config=:config WHERE id=:id',
                ['id' => $id, 'config' => json_encode($config)]
            );
        }
        $this->addSql('ALTER TABLE supla_schedule DROP time_expression, DROP action, DROP action_param');
    }
}
