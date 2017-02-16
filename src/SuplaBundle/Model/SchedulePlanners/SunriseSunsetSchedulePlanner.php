<?php
namespace SuplaBundle\Model\SchedulePlanners;

use Cron\CronExpression;
use SuplaBundle\Entity\Schedule;

class SunriseSunsetSchedulePlanner implements SchedulePlanner
{
    // SR -> SunRise, SS -> SunSet
    const SPECIFICATION_REGEX = '#^S([SR])(-?\d+)#';

    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate)
    {
        preg_match(self::SPECIFICATION_REGEX, $schedule->getCronExpression(), $matches);
        $location = $schedule->getUserTimezone()->getLocation();
        $function = 'date_' . ($matches[1] == 'S' ? 'sunset' : 'sunrise');
        $nextSun = $function($currentDate->getTimestamp(), SUNFUNCS_RET_TIMESTAMP, $location['latitude'], $location['longitude']);
        $nextSun += intval($matches[2]) * 60;
        $nextRunDate = (new \DateTime())->setTimestamp($nextSun);
        if ($nextRunDate <= $currentDate) {
            $nextRunDate->setTime(0, 0);
            $nextRunDate->add(new \DateInterval('P1D'));
            return $this->calculateNextRunDate($schedule, $nextRunDate);
        }
        return $nextRunDate;
    }

    public function canCalculateFor(Schedule $schedule)
    {
        return preg_match(self::SPECIFICATION_REGEX, $schedule->getCronExpression());
    }
}
