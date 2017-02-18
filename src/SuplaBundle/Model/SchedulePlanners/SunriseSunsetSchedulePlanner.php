<?php
namespace SuplaBundle\Model\SchedulePlanners;

use Cron\CronExpression;
use SebastianBergmann\GlobalState\RuntimeException;
use SuplaBundle\Entity\Schedule;

class SunriseSunsetSchedulePlanner implements SchedulePlanner
{
    // SR -> SunRise, SS -> SunSet
    const SPECIFICATION_REGEX = '#^S([SR])(-?\d+)#';

    /** @inheritdoc */
    public function calculateNextRunDate(Schedule $schedule, \DateTime $currentDate)
    {
        $cron = CronExpression::factory($this->getEveryMinuteCronExpression($schedule->getTimeExpression()));
        preg_match(self::SPECIFICATION_REGEX, $schedule->getTimeExpression(), $matches);
        $calculateFromDate = $currentDate;
        if (!$cron->isDue($currentDate)) {
            $calculateFromDate = $cron->getNextRunDate($currentDate);
        }
        $location = $schedule->getUserTimezone()->getLocation();
        $function = 'date_' . ($matches[1] == 'S' ? 'sunset' : 'sunrise');
        $nextSun = $function($calculateFromDate->getTimestamp(), SUNFUNCS_RET_TIMESTAMP, $location['latitude'], $location['longitude']);
        $nextSun += intval($matches[2]) * 60;
        $nextSunRoundTo5Minutes = round($nextSun / 300) * 300;
        $nextRunDate = (new \DateTime('now', $schedule->getUserTimezone()))->setTimestamp($nextSunRoundTo5Minutes);
        if ($nextRunDate <= $currentDate) {
            $nextRunDate->setTime(0, 0);
            $nextRunDate->add(new \DateInterval('P1D'));
            return $this->calculateNextRunDate($schedule, $nextRunDate);
        }
        return $nextRunDate;
    }

    private function getEveryMinuteCronExpression($sunTimeSpec)
    {
        $parts = explode(' ', $sunTimeSpec);
        $parts[0] = '*';
        $parts[1] = '*';
        return implode(' ', $parts);
    }

    public function canCalculateFor(Schedule $schedule)
    {
        return !!preg_match(self::SPECIFICATION_REGEX, $schedule->getTimeExpression());
    }
}
