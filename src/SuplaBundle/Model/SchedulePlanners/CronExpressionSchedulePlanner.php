<?php
namespace SuplaBundle\Model\SchedulePlanners;

use Cron\CronExpression;
use SuplaBundle\Entity\Schedule;

class CronExpressionSchedulePlanner implements SchedulePlanner
{
    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate)
    {
        $cron = CronExpression::factory($schedule->getCronExpression());
        return $cron->getNextRunDate($currentDate);
    }

    public function canCalculateFor(Schedule $schedule)
    {
        return CronExpression::isValidExpression($schedule->getCronExpression());
    }
}
