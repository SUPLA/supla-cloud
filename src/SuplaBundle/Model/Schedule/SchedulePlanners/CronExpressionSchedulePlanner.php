<?php
namespace SuplaBundle\Model\Schedule\SchedulePlanners;

use Cron\CronExpression;
use SuplaBundle\Entity\Schedule;

class CronExpressionSchedulePlanner implements SchedulePlanner
{
    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate)
    {
        return $this->calculateNextRunDateForExpression($schedule->getTimeExpression(), $currentDate);
    }

    public function calculateNextRunDateForExpression($cronExpression, \DateTime $currentDate)
    {
        $cron = CronExpression::factory($cronExpression);
        return $cron->getNextRunDate($currentDate);
    }

    public function canCalculateFor(Schedule $schedule)
    {
        return CronExpression::isValidExpression($schedule->getTimeExpression());
    }
}
